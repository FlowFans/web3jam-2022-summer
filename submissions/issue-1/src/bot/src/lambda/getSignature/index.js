const { createClient } = require('redis');

const redisClient = new createClient({
	url: process.env.REDIS_URL,
});

redisClient
	.on('error', (err) => console.log('Redis Client Error', err));

exports.handler = async (event) => {
	try {
		await redisClient.connect();
	} catch (e) {
		console.log(e);
	}
	
	let body;
	let statusCode = 200;
	const headers = {
		'Content-Type': 'application/json',
	};
	
	const state = event?.queryStringParameters?.state ?? undefined;
	if (state) {
		body = await redisClient.get(state);
	} else {
		statusCode = 404;
		body = JSON.stringify({
			'msg': 'No state or state is expired'
		});
	}
	
	await redisClient.disconnect();
	
	return {
		statusCode,
		body,
		headers,
	};
};
