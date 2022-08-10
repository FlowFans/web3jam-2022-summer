const { SlashCommandBuilder } = require('@discordjs/builders');
const { getIdByUserId, delWalletFromUser } = require('../dynamodb/wakandaplus');
const { MessageActionRow, MessageButton } = require('discord.js');

module.exports = {
	data: new SlashCommandBuilder()
		.setName('deletewallet')
		.setDescription(`Delete a wallet`)
		.addUserOption(option => option.setName('target').setDescription('The wallet to delete')),
	async execute(interaction) {
		const wallet = interaction.options.getUser('target') ?? undefined;
		const id = await getIdByUserId(interaction.user.id)
		const row = new MessageActionRow().addComponents([
			new MessageButton()
				.setCustomId('mywallets')
				.setLabel('« Back to Wallet List')
				.setStyle('SECONDARY'),
		]);
		if (id && wallet) {
			await delWalletFromUser(id, wallet)
			await interaction.update({
				content: `This address has been deleted.`,
				components: [row],
				ephemeral: true,
			});
		} else {
			await interaction.update({
				content: `Something was wrong.`,
				components: [row],
				ephemeral: true,
			});
		}
	},
};
