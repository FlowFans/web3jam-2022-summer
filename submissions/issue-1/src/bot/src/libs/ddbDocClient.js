const { DynamoDBDocumentClient } = require('@aws-sdk/lib-dynamodb');
const { DynamoDBClient } = require('@aws-sdk/client-dynamodb');

const dotenv = require('dotenv');
dotenv.config();

const ddbClient = new DynamoDBClient({
	region: 'ap-northeast-1',
	credentials: {
		accessKeyId: process.env.AWS_ACCESS_KEY,
		secretAccessKey: process.env.AWS_ACCESS_SECRET,
	},
});

const ddbDocClient = DynamoDBDocumentClient.from(ddbClient);

module.exports = ddbDocClient;
