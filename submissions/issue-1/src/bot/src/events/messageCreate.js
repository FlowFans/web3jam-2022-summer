const redisClient = require('../libs/redis.js');
const openai = require('../libs/openai.js');

module.exports = {
	name: 'messageCreate',
	async execute(message) {
		if (message.author.bot) return;
		await message.channel.sendTyping();
		// check if the message is in a channel that is in the list of channels that we want to listen to
		const intention = await redisClient.get(`${message.guildId}-${message.channelId}-${message.author.id}-intention`);
		if (intention) {
			const intentionObj = JSON.parse(intention);
			const response = await openai.createCompletion({
				model: intentionObj.model,
				prompt: message.content,
				temperature: intentionObj.temperature,
				top_p: intentionObj.top_p,
				max_tokens: intentionObj.max_tokens,
				frequency_penalty: intentionObj.frequency_penalty,
				presence_penalty: intentionObj.presence_penalty,
				best_of: intentionObj.best_of,
				user: message.author.id,
				n: intentionObj.n,
				suffix: intentionObj.suffix,
				echo: intentionObj.echo,
			});
			
			await redisClient.del(`${message.guildId}-${message.channelId}-${message.author.id}-intention`);
			
			await message.reply({ content: response.data.choices.map(item => item.text).join('\n')});
		}
	},
};
