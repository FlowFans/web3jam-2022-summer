const { SlashCommandBuilder } = require('@discordjs/builders');
const { MessageActionRow, MessageButton, MessageEmbed } = require('discord.js');

module.exports = {
	data: new SlashCommandBuilder()
		.setName('connectwallet')
		.setDescription('Connect a new wallet'),
	async execute(interaction) {
		const row = new MessageActionRow().addComponents(
			new MessageButton()
				.setCustomId('toConnectWallet')
				.setLabel("Let's go")
				.setStyle('PRIMARY')
		);
		const embed = new MessageEmbed()
			.setTitle('Connect a new wallet')
			.setDescription(
				'This is a read-only connection. Do not share your private keys. We will never ask for your seed phrase.'
			);
		
		await interaction.reply({
			components: [row],
			embeds: [embed],
			ephemeral: true,
		});
	},
};
