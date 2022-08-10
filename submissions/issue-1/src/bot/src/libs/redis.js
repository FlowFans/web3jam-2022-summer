const dotenv = require('dotenv');
const { createClient } = require('redis');
dotenv.config();

const client = new createClient({
	url: process.env.REDIS_URL,
});

client.on('error', (err) => console.log('Redis Client Error', err));

module.exports = client;
