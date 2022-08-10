const {
	PutCommand,
	GetCommand,
	DeleteCommand,
	QueryCommand,
	UpdateCommand,
} = require('@aws-sdk/lib-dynamodb');
const ddbDocClient = require('../libs/ddbDocClient.js');
const redisClient = require('../libs/redis.js');

// 用于创建用户存档，危险！会覆盖其他字段，更新使用 Update
const putUser = async (id, user_id, guild_id) => {
	const params = {
		TableName: 'wakandaplus',
		Item: {
			id: BigInt(id),
			user: BigInt(user_id),
			guild: BigInt(guild_id),
		},
	};

	try {
		const data = await ddbDocClient.send(new PutCommand(params));
		console.log('Success - item added or updated:\n', data)
		return data;
	} catch (err) {
		console.log(err.stack)
		return false;
	}
};

// 查询用户信息
const getUser = async (id) => {
	const params = {
		TableName: 'wakandaplus',
		Key: {
			id: BigInt(id),
		},
	};

	try {
		return await ddbDocClient.send(new GetCommand(params));
	} catch (err) {
		console.log(err.stack)
		return false;
	}
};

const deleteUserById = async (user_id, guild_id) => {
	const params = {
		TableName: 'wakandaplus',
		Key: {
			user: BigInt(user_id),
			guild: BigInt(guild_id),
		},
	};

	try {
		await ddbDocClient.send(new DeleteCommand(params));
		console.log('Success - item deleted')
		return true;
	} catch (err) {
		console.log(err)
		return false;
	}
};

const addWalletToUser = async (id, address) => {
	// Set the parameters.
	const params = {
		TableName: 'wakandaplus',
		Key: {
			id: BigInt(id),
		},
		ExpressionAttributeNames: { '#wallets': 'wallets' },
		UpdateExpression: 'ADD #wallets :wallets',
		ExpressionAttributeValues: {
			':wallets': new Set([address]),
		},
	};
	try {
		const data = await ddbDocClient.send(new UpdateCommand(params));
		console.log('Success - item added or updated:\n', data);
		return data;
	} catch (err) {
		console.log(err);
		return false;
	}
};

const delWalletFromUser = async (id, address) => {
	const params = {
		TableName: 'wakandaplus',
		Key: {
			id: BigInt(id),
		},
		ExpressionAttributeNames: { '#wallets': 'wallets' },
		UpdateExpression: 'DELETE #wallets :wallets',
		ExpressionAttributeValues: {
			':wallets': new Set([address]),
		},
	};
	try {
		const res = await ddbDocClient.send(new UpdateCommand(params));
		console.log('Success - delete', res);
		return true;
	} catch (err) {
		console.log(err);
		return false;
	}
}

const queryUser = async (user_id) => {
	const params = {
		ExpressionAttributeNames: { '#user': 'user' },
		ProjectionExpression: 'id, #user',
		TableName: 'wakandaplus',
		IndexName: 'user-index',
		KeyConditionExpression: '#user = :user',
		ExpressionAttributeValues: {
			':user': BigInt(user_id),
		},
	};

	try {
		return await ddbDocClient.send(new QueryCommand(params));
	} catch (err) {
		return false;
	}
};

const getIdByUserId = async (user_id) => {
	let id = await redisClient.get(user_id);
	if (!id) {
		const res = await ddbDocClient.send(new QueryCommand({
			ExpressionAttributeNames: { '#user': 'user' },
			ProjectionExpression: 'id, #user',
			TableName: 'wakandaplus',
			IndexName: 'user-index',
			KeyConditionExpression: '#user = :user',
			ExpressionAttributeValues: {
				':user': BigInt(user_id),
			},
		}));
		if (res.Count > 0) {
			id = res.Items[0].id;
			await redisClient.set(
				user_id,
				id.toString(),
				{
					EX: 86400,
				},
			);
		}
	}
	return id
}

module.exports = {
	putUser,
	getUser,
	deleteUserById,
	queryUser,
	addWalletToUser,
	getIdByUserId,
	delWalletFromUser
};
