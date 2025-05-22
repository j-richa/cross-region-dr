import boto3
import json

def lambda_handler(event, context):
    secrets_manager_client = boto3.client('secretsmanager')
    secret_name = 'db-secret'
    new_db_endpoint = 'new-database-endpoint-for-secondary-region'

    # Retrieve the current secret value
    get_secret_value_response = secrets_manager_client.get_secret_value(SecretId=secret_name)
    secret = get_secret_value_response['SecretString']
    secret_dict = json.loads(secret)

    # Update the database endpoint in the secret
    secret_dict['host'] = new_db_endpoint

    # Update the secret in Secrets Manager
    secrets_manager_client.update_secret(SecretId=secret_name, SecretString=json.dumps(secret_dict))

    return {
        'statusCode': 200,
        'body': json.dumps('Secret updated successfully')
    }
