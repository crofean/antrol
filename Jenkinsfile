pipeline {
    agent any
    
    environment {
        // Define environment variables
        APP_NAME = 'antrol'
        REMOTE_USER = 'deploy'
        REMOTE_HOST = 'your-production-server.com' // Replace with your server hostname or IP
        REMOTE_DIR = '/var/www/antrol'
        BACKUP_DIR = '/var/www/backups/antrol'
        TIMESTAMP = sh(script: 'date +%Y%m%d_%H%M%S', returnStdout: true).trim()
    }
    
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        
        stage('Install Dependencies') {
            steps {
                sh 'composer install --no-dev --optimize-autoloader'
                sh 'npm ci'
                sh 'npm run build'
            }
        }
        
        stage('Run Tests') {
            steps {
                sh 'php artisan test'
            }
        }
        
        stage('Create Backup on Remote Server') {
            steps {
                script {
                    // Create backup directory if it doesn't exist
                    sh """
                        ssh ${REMOTE_USER}@${REMOTE_HOST} '
                        mkdir -p ${BACKUP_DIR}
                        if [ -d "${REMOTE_DIR}" ]; then
                            tar -czf ${BACKUP_DIR}/${APP_NAME}_backup_${TIMESTAMP}.tar.gz -C \$(dirname ${REMOTE_DIR}) \$(basename ${REMOTE_DIR})
                            echo "Backup created at ${BACKUP_DIR}/${APP_NAME}_backup_${TIMESTAMP}.tar.gz"
                        else
                            echo "Remote directory ${REMOTE_DIR} does not exist yet. No backup created."
                        fi
                        '
                    """
                }
            }
        }
        
        stage('Prepare Deployment Package') {
            steps {
                script {
                    // Create deployment archive excluding development files
                    sh """
                        rm -rf node_modules vendor
                        composer install --no-dev --optimize-autoloader
                        tar -czf ${APP_NAME}_deploy.tar.gz \
                            --exclude='.git' \
                            --exclude='.github' \
                            --exclude='node_modules' \
                            --exclude='tests' \
                            --exclude='.env' \
                            --exclude='storage/logs/*' \
                            --exclude='storage/framework/cache/*' \
                            --exclude='storage/framework/sessions/*' \
                            --exclude='storage/framework/views/*' \
                            .
                    """
                }
            }
        }
        
        stage('Deploy to Production') {
            steps {
                script {
                    // Create a temporary deployment directory
                    def tempDir = "/tmp/${APP_NAME}_${TIMESTAMP}"
                    
                    // Transfer the deployment package
                    sh "scp ${APP_NAME}_deploy.tar.gz ${REMOTE_USER}@${REMOTE_HOST}:/tmp/"
                    
                    // Extract and deploy
                    sh """
                        ssh ${REMOTE_USER}@${REMOTE_HOST} '
                        set -e
                        
                        # Create temporary directory
                        mkdir -p ${tempDir}
                        
                        # Extract deployment package
                        tar -xzf /tmp/${APP_NAME}_deploy.tar.gz -C ${tempDir}
                        
                        # Ensure target directory exists
                        mkdir -p ${REMOTE_DIR}
                        
                        # Preserve storage and .env
                        if [ -d "${REMOTE_DIR}/storage" ]; then
                            cp -R ${REMOTE_DIR}/storage ${tempDir}/
                        fi
                        
                        if [ -f "${REMOTE_DIR}/.env" ]; then
                            cp ${REMOTE_DIR}/.env ${tempDir}/
                        fi
                        
                        # Replace files
                        rsync -av --delete ${tempDir}/ ${REMOTE_DIR}/
                        
                        # Set proper permissions
                        find ${REMOTE_DIR} -type f -exec chmod 644 {} \\;
                        find ${REMOTE_DIR} -type d -exec chmod 755 {} \\;
                        
                        # Make storage and bootstrap/cache writable
                        chmod -R 775 ${REMOTE_DIR}/storage
                        chmod -R 775 ${REMOTE_DIR}/bootstrap/cache
                        
                        # Update ownership if web server runs as www-data
                        chown -R www-data:www-data ${REMOTE_DIR}
                        
                        # Clean up
                        rm -f /tmp/${APP_NAME}_deploy.tar.gz
                        rm -rf ${tempDir}
                        
                        # Run Laravel deployment commands
                        cd ${REMOTE_DIR}
                        php artisan cache:clear
                        php artisan config:cache
                        php artisan route:cache
                        php artisan view:cache
                        php artisan migrate --force
                        
                        echo "Deployment completed successfully"
                        '
                    """
                }
            }
            post {
                failure {
                    // Roll back if deployment fails
                    sh """
                        ssh ${REMOTE_USER}@${REMOTE_HOST} '
                        if [ -f "${BACKUP_DIR}/${APP_NAME}_backup_${TIMESTAMP}.tar.gz" ]; then
                            echo "Deployment failed! Rolling back to previous version..."
                            
                            # Remove the failed deployment
                            rm -rf ${REMOTE_DIR}
                            mkdir -p ${REMOTE_DIR}
                            
                            # Restore from backup
                            tar -xzf ${BACKUP_DIR}/${APP_NAME}_backup_${TIMESTAMP}.tar.gz -C \$(dirname ${REMOTE_DIR})
                            
                            # Restart services if needed
                            sudo systemctl restart php-fpm nginx
                            
                            echo "Rollback completed successfully"
                        else
                            echo "No backup found for rollback!"
                        fi
                        '
                    """
                }
            }
        }
        
        stage('Verify Deployment') {
            steps {
                // Simple verification - check if the application is responding
                sh """
                    ssh ${REMOTE_USER}@${REMOTE_HOST} '
                    curl -s -o /dev/null -w "%{http_code}" http://localhost | grep 200
                    '
                """
            }
        }
        
        stage('Cleanup Old Backups') {
            steps {
                // Keep only the last 5 backups to save disk space
                sh """
                    ssh ${REMOTE_USER}@${REMOTE_HOST} '
                    cd ${BACKUP_DIR} && ls -t ${APP_NAME}_backup_*.tar.gz | tail -n +6 | xargs rm -f
                    '
                """
            }
        }
    }
    
    post {
        success {
            echo "Deployment successful! Your Laravel application is now live."
        }
        failure {
            echo "Deployment failed! The system has been rolled back to the previous version."
        }
        always {
            // Clean up local deployment files
            sh "rm -f ${APP_NAME}_deploy.tar.gz"
        }
    }
}