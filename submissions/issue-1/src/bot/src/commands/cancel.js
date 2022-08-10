const { SlashCommandBuilder } = require('@discordjs/builders');
const redisClient = require('../libs/redis.js');

module.exports = {
	data: new SlashCommandBuilder()
		.setName('cancel')
		.setDescription('Cancel a command.'),
	async execute(interaction) {
		const res = await redisClient.get(`${interaction.guildId}-${interaction.channelId}-${interaction.user.id}-intention`);
		if (res) {
			try {
				await redisClient.del(`${interaction.guildId}-${interaction.channelId}-${interaction.user.id}-intention`);
				interaction.reply({ content: 'Command cancelled.', ephemeral: true });
			} catch (e) {
				interaction.reply({ content: 'There was an error trying to cancel the command.', ephemeral: true });
			}
		} else {
			interaction.reply({ content: 'There is no command to cancel.', ephemeral: true });
		}
	},
};
