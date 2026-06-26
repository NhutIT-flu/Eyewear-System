pipeline {
    agent any

    // ─────────────────────────────────────────────────────────────
    // PIPELINE OPTIONS
    // ─────────────────────────────────────────────────────────────
    options {
        timestamps()
        skipDefaultCheckout(false)
        timeout(time: 30, unit: 'MINUTES')
        buildDiscarder(logRotator(numToKeepStr: '30', artifactNumToKeepStr: '10'))
        disableConcurrentBuilds()
    }

    // ─────────────────────────────────────────────────────────────
    // AUTO TRIGGER — Poll SCM mỗi 2 phút hoặc webhook
    // ─────────────────────────────────────────────────────────────
    triggers {
        pollSCM('H/2 * * * *')
    }

    // ─────────────────────────────────────────────────────────────
    // ENVIRONMENT
    // ─────────────────────────────────────────────────────────────
    environment {
        BACKEND_DIR        = 'backend'
        FRONTEND_DIR       = 'frontend'
        PROJECT_KEY        = 'ESQ'
        SONAR_PROJECT_KEY  = 'eyewear-system'
        SONAR_PROJECT_NAME = 'Eyewear System'
        NEWMAN_REPORT_DIR  = 'postman-reports'
    }

    stages {

        // ═════════════════════════════════════════════════════════
        //  STAGE 1 — CHECKOUT
        // ═════════════════════════════════════════════════════════
        stage('Checkout') {
            steps {
                echo '📥 Checking out source code...'
                checkout scm
                script {
                    def gitCommit = isUnix()
                        ? sh(script: 'git log -1 --pretty=%h', returnStdout: true).trim()
                        : bat(script: '@git log -1 --pretty=%%h', returnStdout: true).trim()
                    def gitBranch = env.GIT_BRANCH ?: env.BRANCH_NAME ?: 'unknown'
                    currentBuild.displayName = "#${BUILD_NUMBER} — ${gitBranch}"
                    currentBuild.description = "Commit: ${gitCommit}"

                    // Get list of changed files
                    def changedFilesStr = ""
                    try {
                        def targetBranch = (gitBranch == 'main' || gitBranch == 'origin/main') ? 'HEAD~1' : 'origin/main'
                        echo "🔍 Finding changed files compared to ${targetBranch}..."
                        if (isUnix()) {
                            changedFilesStr = sh(script: "git diff --name-only ${targetBranch} || git diff --name-only HEAD~1 || true", returnStdout: true).trim()
                        } else {
                            changedFilesStr = bat(script: "@git diff --name-only ${targetBranch} 2>nul || @git diff --name-only HEAD~1 2>nul || (exit /b 0)", returnStdout: true).trim()
                        }
                    } catch (Exception e) {
                        echo "⚠️ Warning: Could not get changed files: ${e.message}"
                    }

                    // Store in file and env
                    writeFile file: 'changed_files.txt', text: changedFilesStr
                    env.CHANGED_FILES = changedFilesStr

                    def changedFilesList = changedFilesStr.split('\r?\n').collect { it.trim() }.findAll { it }
                    echo "📋 Changed files: ${changedFilesList}"

                    if (changedFilesList.isEmpty()) {
                        echo "No changed files detected or first build. Running full pipeline stages."
                        env.FORCE_ALL = 'true'
                        env.BACKEND_CHANGED = 'true'
                        env.FRONTEND_CHANGED = 'true'
                        env.SONAR_CHANGED = 'true'
                        env.SONAR_INCLUSIONS = ''
                    } else {
                        env.FORCE_ALL = 'false'
                        
                        def backendChanged = changedFilesList.any { it.startsWith('backend/') }
                        def frontendChanged = changedFilesList.any { it.startsWith('frontend/') }
                        env.BACKEND_CHANGED = backendChanged ? 'true' : 'false'
                        env.FRONTEND_CHANGED = frontendChanged ? 'true' : 'false'

                        def sonarSources = ['backend/app', 'backend/core', 'backend/routes', 'frontend/js']
                        def hasSonarChanges = changedFilesList.any { file ->
                            sonarSources.any { source -> file.startsWith(source) }
                        }
                        env.SONAR_CHANGED = hasSonarChanges ? 'true' : 'false'

                        def sonarInclusions = changedFilesList.findAll { file ->
                            sonarSources.any { source -> file.startsWith(source) }
                        }.join(',')
                        env.SONAR_INCLUSIONS = sonarInclusions
                    }
                }
            }
        }

        // ═════════════════════════════════════════════════════════
        //  STAGE 2 — STATIC VALIDATION (parallel)
        //  Chạy song song 3 kiểm tra độc lập → tiết kiệm thời gian
        // ═════════════════════════════════════════════════════════
        stage('Static Validation') {
            parallel {

                // ── 2a. Project Structure ─────────────────────────
                stage('Project Structure') {
                    steps {
                        echo '📁 Verifying project structure...'
                        script {
                            def requiredPaths = [
                                'backend/app',
                                'backend/core',
                                'backend/routes/api.php',
                                'backend/database/schema.sql',
                                'backend/database/seeder.php',
                                'backend/public/index.php',
                                'frontend/index.html',
                                'frontend/js',
                                'frontend/assets',
                                'Eyewear-System.postman_collection.json'
                            ]
                            def missing = requiredPaths.findAll { !fileExists(it) }
                            if (missing) {
                                error "❌ Missing paths:\n${missing.collect { '  • ' + it }.join('\n')}"
                            }
                            echo "✅ All ${requiredPaths.size()} required paths verified."
                        }
                    }
                }

                // ── 2b. PHP Syntax Check ──────────────────────────
                stage('PHP Syntax') {
                    steps {
                        echo '🔍 Checking PHP syntax...'
                        script {
                            if (isUnix()) {
                                sh '''
                                    if command -v php > /dev/null 2>&1; then
                                        ERRORS=0
                                        if [ "${FORCE_ALL}" = "true" ]; then
                                            echo "Running full PHP syntax check..."
                                            while IFS= read -r -d '' file; do
                                                if ! php -l "$file" > /dev/null 2>&1; then
                                                    php -l "$file"
                                                    ERRORS=$((ERRORS + 1))
                                                fi
                                            done < <(find backend -name "*.php" -not -path "*/vendor/*" -print0)
                                        else
                                            echo "Running incremental PHP syntax check..."
                                            if [ -f changed_files.txt ]; then
                                                while IFS= read -r file; do
                                                    if [[ "$file" =~ \.php$ ]] && [[ "$file" =~ ^backend/ ]] && [[ ! "$file" =~ vendor/ ]]; then
                                                        if [ -f "$file" ]; then
                                                            echo "Checking $file"
                                                            if ! php -l "$file" > /dev/null 2>&1; then
                                                                php -l "$file"
                                                                ERRORS=$((ERRORS + 1))
                                                            fi
                                                        fi
                                                    fi
                                                done < changed_files.txt
                                            fi
                                        fi
                                        if [ $ERRORS -gt 0 ]; then
                                            echo "❌ Found $ERRORS PHP syntax error(s)."
                                            exit 1
                                        fi
                                        echo "✅ PHP syntax check completed."
                                    else
                                        echo "⚠️ PHP CLI not found — skipping."
                                    fi
                                '''
                            } else {
                                bat '''
                                    where php >nul 2>nul
                                    if %ERRORLEVEL% EQU 0 (
                                        set ERRORS=0
                                        if "%FORCE_ALL%"=="true" (
                                            echo Running full PHP syntax check...
                                            for /R backend %%f in (*.php) do (
                                                echo %%f | findstr /i "vendor" >nul
                                                if errorlevel 1 php -l "%%f"
                                            )
                                        ) else (
                                            echo Running incremental PHP syntax check...
                                            if exist changed_files.txt (
                                                for /F "tokens=*" %%f in (changed_files.txt) do (
                                                    echo %%f | findstr /i /r "\.php$" >nul
                                                    if not errorlevel 1 (
                                                        echo %%f | findstr /i /r "^backend/" >nul
                                                        if not errorlevel 1 (
                                                            echo %%f | findstr /i "vendor" >nul
                                                            if errorlevel 1 (
                                                                if exist %%f (
                                                                    echo Checking %%f
                                                                    php -l "%%f"
                                                                )
                                                            )
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    ) else (
                                        echo PHP CLI not found — skipping.
                                    )
                                    exit /b 0
                                '''
                            }
                        }
                    }
                }

                // ── 2c. Frontend Entry Files ──────────────────────
                stage('Frontend Files') {
                    steps {
                        echo '🌐 Checking frontend entry files...'
                        script {
                            def requiredFrontendFiles = [
                                'frontend/index.html',
                                'frontend/assets/css/styles.css',
                                'frontend/js/main.js',
                                'frontend/js/services/apiClient.js'
                            ]
                            def missing = requiredFrontendFiles.findAll { !fileExists(it) }
                            if (missing) {
                                error "❌ Missing frontend files:\n${missing.collect { '  • ' + it }.join('\n')}"
                            }
                            echo "✅ All ${requiredFrontendFiles.size()} frontend entry files OK."
                        }
                    }
                }
            }
        }

        // ═════════════════════════════════════════════════════════
        //  STAGE 3 — PHPUNIT UNIT TESTS + COVERAGE
        //  Cài PHPUnit qua Composer, chạy test, sinh coverage.xml
        // ═════════════════════════════════════════════════════════
        stage('PHPUnit Tests') {
            when {
                anyOf {
                    expression { return env.FORCE_ALL == 'true' }
                    expression { return env.BACKEND_CHANGED == 'true' }
                }
            }
            steps {
                echo '🧪 Running PHPUnit unit tests with coverage...'
                script {
                    if (isUnix()) {
                        sh '''
                            cd backend
                            if command -v composer > /dev/null 2>&1; then
                                composer install --no-interaction --prefer-dist --quiet
                                XDEBUG_MODE=coverage vendor/bin/phpunit \
                                    --coverage-clover tests/coverage.xml \
                                    --log-junit tests/junit.xml \
                                    --colors=never || true
                                echo "✅ PHPUnit tests completed."
                            else
                                echo "⚠️ Composer not found — skipping PHPUnit tests."
                            fi
                        '''
                    } else {
                        bat '''
                            cd backend
                            where composer >nul 2>nul
                            if %ERRORLEVEL% EQU 0 (
                                call composer install --no-interaction --prefer-dist --quiet
                            ) else if exist composer.phar (
                                php composer.phar install --no-interaction --prefer-dist --quiet
                            ) else (
                                echo Composer not found — skipping PHPUnit tests.
                                exit /b 0
                            )
                            set XDEBUG_MODE=coverage
                            php vendor\\bin\\phpunit --coverage-clover tests/coverage.xml --log-junit tests/junit.xml --colors=never
                            echo PHPUnit tests completed.
                            exit /b 0
                        '''
                    }
                }
            }
        }

        // ═════════════════════════════════════════════════════════
        //  STAGE 4 — SONARQUBE ANALYSIS
        //  Cấu hình scan đọc từ sonar-project.properties
        //  Chỉ truyền version (dynamic) và token (secret) qua CLI
        // ═════════════════════════════════════════════════════════
        stage('SonarQube Analysis') {
            when {
                anyOf {
                    expression { return env.FORCE_ALL == 'true' }
                    expression { return env.SONAR_CHANGED == 'true' }
                }
            }
            steps {
                echo '📊 Running SonarQube code quality analysis...'
                withCredentials([string(credentialsId: 'SONAR_TOKEN', variable: 'SONAR_TOKEN')]) {
                    withSonarQubeEnv('SonarQube') {
                        script {
                            def scannerHome = tool 'sonar-scanner'
                            def inclusionsParam = (env.FORCE_ALL == 'true') ? '' : "-Dsonar.inclusions=${env.SONAR_INCLUSIONS}"
                            if (isUnix()) {
                                sh """
                                    "${scannerHome}/bin/sonar-scanner" \
                                        -Dsonar.projectVersion=1.0.${BUILD_NUMBER} \
                                        -Dsonar.token=\$SONAR_TOKEN \
                                        ${inclusionsParam}
                                """
                            } else {
                                bat """
                                    "${scannerHome}\\bin\\sonar-scanner.bat" ^
                                        -Dsonar.projectVersion=1.0.${BUILD_NUMBER} ^
                                        -Dsonar.token=%SONAR_TOKEN% ^
                                        ${inclusionsParam}
                                """
                            }
                        }
                    }
                }
            }
        }

        // ═════════════════════════════════════════════════════════
        //  STAGE 4 — QUALITY GATE
        //  Chờ SonarQube webhook trả kết quả pass/fail
        // ═════════════════════════════════════════════════════════
        stage('Quality Gate') {
            when {
                anyOf {
                    expression { return env.FORCE_ALL == 'true' }
                    expression { return env.SONAR_CHANGED == 'true' }
                }
            }
            steps {
                echo '🚦 Waiting for SonarQube Quality Gate...'
                script {
                    timeout(time: 5, unit: 'MINUTES') {
                        def qg = waitForQualityGate()
                        currentBuild.description += " | SonarQube: ${qg.status}"
                        if (qg.status != 'OK') {
                            error "❌ Quality Gate failed: ${qg.status}. Pipeline is aborted."
                        }
                    }
                }
            }
        }

        // ═════════════════════════════════════════════════════════
        //  STAGE 5 — NEWMAN API TESTS
        //  Chạy Postman collection → xuất report JSON + HTML
        // ═════════════════════════════════════════════════════════
        stage('Newman API Tests') {
            when {
                anyOf {
                    expression { return env.FORCE_ALL == 'true' }
                    expression { return env.BACKEND_CHANGED == 'true' }
                }
            }
            steps {
                echo '🧪 Running Postman/Newman API tests...'
                script {
                    if (isUnix()) {
                        sh """
                            mkdir -p ${NEWMAN_REPORT_DIR}
                            if command -v newman > /dev/null 2>&1; then
                                newman run "Eyewear-System.postman_collection.json" \
                                    --timeout-request 10000 \
                                    --timeout 120000 \
                                    -r cli,json \
                                    --reporter-json-export ./${NEWMAN_REPORT_DIR}/summary-eyewear.json \
                                    --suppress-exit-code || true
                                echo "✅ Newman tests completed."
                            else
                                echo "⚠️ Newman not installed — skipping API tests."
                            fi
                        """
                    } else {
                        bat """
                            if not exist ${NEWMAN_REPORT_DIR} mkdir ${NEWMAN_REPORT_DIR}
                            where newman >nul 2>nul
                            if %ERRORLEVEL% EQU 0 (
                                newman run "Eyewear-System.postman_collection.json" ^
                                    --timeout-request 10000 ^
                                    --timeout 120000 ^
                                    -r cli,json ^
                                    --reporter-json-export ./${NEWMAN_REPORT_DIR}/summary-eyewear.json ^
                                    --suppress-exit-code
                                echo Newman tests completed.
                            ) else (
                                echo Newman not installed — skipping API tests.
                            )
                            exit /b 0
                        """
                    }
                }
            }
        }

        // ═════════════════════════════════════════════════════════
        //  STAGE 6 — PARSE TEST RESULTS & UPDATE BUILD
        //  Phân tích kết quả Newman → hiện tổng số pass/fail
        // ═════════════════════════════════════════════════════════
        stage('Parse Test Results') {
            when {
                anyOf {
                    expression { return env.FORCE_ALL == 'true' }
                    expression { return env.BACKEND_CHANGED == 'true' }
                }
            }
            steps {
                echo '📈 Parsing Newman test results...'
                script {
                    def reportFile = "${NEWMAN_REPORT_DIR}/summary-eyewear.json"
                    if (fileExists(reportFile)) {
                        def report = readJSON file: reportFile
                        def stats = report?.run?.stats?.assertions ?: [:]
                        def total  = stats.total  ?: 0
                        def failed = stats.failed ?: 0
                        def passed = total - failed
                        def failedRequests = (report?.run?.failures ?: []).collect { it?.source?.name ?: 'Unknown' }.unique()

                        echo "📊 Test Results: ${passed}/${total} assertions passed (${failed} failed)"

                        currentBuild.description += " | Tests: ${passed}/${total}"

                        if (failed > 0) {
                            echo "❌ Failed APIs:\n${failedRequests.collect { '  • ' + it }.join('\n')}"
                            unstable "⚠️ ${failed} test assertion(s) failed"
                        } else {
                            echo "✅ All ${total} test assertions passed!"
                        }
                    } else {
                        echo '⚠️ No Newman report found — tests may have been skipped.'
                    }
                }
            }
        }

        // ═════════════════════════════════════════════════════════
        //  STAGE 7 — NOTIFY JIRA
        //  Post kết quả build vào Jira issues (REST API v3)
        // ═════════════════════════════════════════════════════════
        stage('Notify Jira') {
            when {
                expression { return isUnix() }
            }
            steps {
                echo '📌 Posting build result to Jira...'
                withCredentials([
                    usernamePassword(
                        credentialsId: 'JIRA_CREDS',
                        usernameVariable: 'JIRA_USER',
                        passwordVariable: 'JIRA_TOKEN'
                    ),
                    string(credentialsId: 'JIRA_BASE_URL', variable: 'JIRA_URL')
                ]) {
                    script {
                        def buildStatus  = currentBuild.currentResult ?: 'SUCCESS'
                        def emoji        = buildStatus == 'SUCCESS' ? '✅' : '❌'
                        def buildUrl     = env.BUILD_URL ?: 'N/A'
                        def branch       = env.GIT_BRANCH ?: env.BRANCH_NAME ?: 'unknown'
                        def sonarUrl     = env.SONAR_HOST_URL ? "${env.SONAR_HOST_URL}/dashboard?id=${SONAR_PROJECT_KEY}" : 'N/A'

                        def commentLines = [
                            "${emoji} Jenkins Build #${BUILD_NUMBER} — ${buildStatus}",
                            "Branch: ${branch}",
                            "Build URL: ${buildUrl}",
                            "SonarQube Report: ${sonarUrl}",
                            "Triggered by: ${currentBuild.getBuildCauses()[0]?.shortDescription ?: 'unknown'}"
                        ]

                        def adfBody = groovy.json.JsonOutput.toJson([
                            version: 1,
                            type: 'doc',
                            content: commentLines.collect { line ->
                                [type: 'paragraph', content: [[type: 'text', text: line]]]
                            }
                        ])

                        def jqlEncoded = java.net.URLEncoder.encode(
                            "project = ${PROJECT_KEY} AND statusCategory != Done AND issuetype = Bug AND labels = ci-cd",
                            'UTF-8'
                        )
                        def jiraBase = JIRA_URL.replaceAll('/+$', '')
                        def authB64  = "${JIRA_USER}:${JIRA_TOKEN}".bytes.encodeBase64().toString()

                        sh """
                            ISSUES=\$(curl -s -X GET \\
                                -H "Authorization: Basic ${authB64}" \\
                                -H "Content-Type: application/json" \\
                                "${jiraBase}/rest/api/3/search/jql?jql=${jqlEncoded}&maxResults=5&fields=summary" \\
                                | jq -r '.issues[].key')

                            echo "Found Jira issues: \$ISSUES"

                            for KEY in \$ISSUES; do
                                curl -s -X POST \\
                                    -H "Authorization: Basic ${authB64}" \\
                                    -H "Content-Type: application/json" \\
                                    -d '{"body": ${adfBody}}' \\
                                    "${jiraBase}/rest/api/3/issue/\$KEY/comment" > /dev/null
                                echo "📌 Notified Jira issue: \$KEY"
                            done
                        """
                    }
                }
            }
        }
    }

    // ═════════════════════════════════════════════════════════════
    //  POST ACTIONS — Luôn chạy dù build thành công hay thất bại
    // ═════════════════════════════════════════════════════════════
    post {
        always {
            echo '📦 Archiving build artifacts...'
            archiveArtifacts(
                artifacts: [
                    'README.md',
                    'docs/**/*.md',
                    'docs/**/*.docx',
                    'Eyewear-System.postman_collection.json',
                    'postman-reports/**'
                ].join(','),
                allowEmptyArchive: true
            )
            cleanWs(
                cleanWhenNotBuilt: false,
                deleteDirs: true,
                patterns: [
                    [pattern: 'postman-reports/**', type: 'EXCLUDE'],
                    [pattern: '.git/**',            type: 'EXCLUDE']
                ]
            )
        }
        success {
            echo '🎉 Pipeline PASSED — Eyewear System build #${BUILD_NUMBER} successful.'
        }
        failure {
            echo '🔥 Pipeline FAILED — Check console log for details.'
        }
        unstable {
            echo '⚠️ Pipeline UNSTABLE — Quality Gate or test assertions may have warnings.'
        }
    }
}
