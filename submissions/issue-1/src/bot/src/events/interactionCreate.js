const { MessageActionRow, MessageButton } = require('discord.js');
const client = require('../libs/redis.js');
const randomString = require('../utils/randomString.js');
const { isAddress, shortenAddress } = require('../utils/address');
const { getUser } = require('../dynamodb/wakandaplus.js');
const { getIdByUserId, delWalletFromUser } = require('../dynamodb/wakandaplus');

module.exports = {
	name: 'interactionCreate',
	async execute(interaction) {
		if (!interaction.isButton()) return;
		if (interaction.customId === 'toConnectWallet') {
			const user = interaction.user.id;
			const state = randomString(12);
			const message = `${interaction.member.displayName.toUpperCase()} want to connect wallet at Wakanda Metaverse. ${new Date().toLocaleString()}.`;
			await client.set(
				state,
				JSON.stringify({
					user: user,
					message: message,
				}),
				{
					EX: 300,
				},
			);
			
			const row = new MessageActionRow().addComponents(
				new MessageButton()
					.setLabel('Connect Wallet')
					.setURL(`https://wakandaplus.wakanda-labs.com/#/sign/${state}`)
					.setStyle('LINK'),
			);
			
			await interaction.reply({
				content: `**Sign the message below in 5 min:**\`\`\`${message}\`\`\`
Make sure you sign the message and **NEVER** share your seed phrase or private key.`,
				embeds: [],
				components: [row],
				ephemeral: true,
			});
		}
		else if (isAddress(interaction.customId)) {
			const row = new MessageActionRow().addComponents([
				new MessageButton()
					.setCustomId(`deletewallet-${interaction.customId}`)
					.setLabel('Delete wallet')
					.setStyle('SECONDARY'),
				new MessageButton()
					.setCustomId('mywallets')
					.setLabel('« Back to Wallet List')
					.setStyle('SECONDARY'),
			]);
			
			await interaction.update({
				content: `You select ${shortenAddress(interaction.customId)}`,
				components: [row],
				ephemeral: true,
			});
		}
		else if (interaction.customId === 'mywallets') {
			const user = interaction.user;
			const id = await getIdByUserId(user.id);
			if (id) {
				const q = await getUser(id);
				const info = q.Item;
				if (info) {
					const wallets = info['wallets'] ? Array.from(info['wallets']) : [];
					const row = new MessageActionRow().addComponents(
						wallets.slice(0, 4).map((address) => new MessageButton()
							.setCustomId(address)
							.setLabel(shortenAddress(address))
							.setStyle('SECONDARY'),
						).concat(wallets.length > 4 ?
							[new MessageButton()
								.setCustomId('next')
								.setLabel('»')] : [],
						),
					);
					await interaction.update({
						content: 'Choose a wallet from the list below:',
						components: [row],
						ephemeral: true,
					});
				}
			}
			else {
				await interaction.update({
					content: 'None address here.',
					ephemeral: true,
				});
			}
		}
		else if (interaction.customId.slice(0, 12) === 'deletewallet') {
			const wallet = interaction.customId.slice(13)
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
		}
	},
};
