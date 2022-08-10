const { SlashCommandBuilder } = require('@discordjs/builders');

module.exports = {
	data: new SlashCommandBuilder()
		.setName('help')
		.setDescription(`Get effective help from bot`),
	async execute(interaction) {
		await interaction.reply({
			content: `I can help you connect and manage wallets.
You can control me by sending these commands:

*/connectwallet* - connect a new wallet
*/mywallets* - manage your wallets

**Edit Wallets**
*/deletewallet* - delete a wallet

**Account**
*/balanceOf* - get balance of a wallet

**AI**
*/openai* - call a OpenAI, default use 'text-davinci-002' model

**Other**
*/cancel* - cancel a command
*/prune* - prune up to 99 messages
`,
			ephemeral: true,
		});
	},
};
