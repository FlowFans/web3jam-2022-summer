# Drizzle


# Links

1. Website: https://www.drizzle33.app/
2. Walkthrough video: https://drive.google.com/file/d/1sitLXaEgPbySn9AQsr-NUd_X70x5AbJz/view?usp=sharing
3. Deck: please check /docs/deck.pdf

## What’s Drizzle?

Drizzle is a Token Distribution Tool #onFLOW! Anyone can use it to create a DROP or start an NFT Raffle in a minute!

Token distribution is a common need in crypto space. A project may want to airdrop to its community; A DAO may need to reward some contributors; A member of a Discord channel may want to distribute some tokens to other members to show appreciation or just for fun. 

But currently it’s a bit tedious and annoying. You need to collect an access-list, run a program locally or use a centralized service to determine how much an account in the access-list should get or who are the winners(well, this is more than trivial, this is not transparent), and at last transfer to the eligible accounts or winners one by one, or use bulk transfer tools like **[bayou](https://www.bayou33.app)**. 

Drizzle aims to make this simple! You can collect your access-list by using **[FLOAT](https://floats.city)** or **[hype.day](https://hype.day)**, and then create a DROP or NFT Raffle on chain in less than one minute by using Drizzle! Fast & Transparent!

## So, what’s DROP?

DROP means airdrop in Drizzle. Currently we support the airdrop of FungibleToken only. 

The main process of a DROP is quite simple:

- You create a new DROP by setting the basic information, depositing funds, setting the criteria for eligible accounts and token distribution mode, then share the DROP link to your community;
- Community members access the DROP page via the link, check their eligibility and claim the token if they are eligible.

To decide who is eligible for your DROP, you can check **[here](#who-is-eligible)**.

For setting the token distribution mode, you have two choices:

- Identical: All claimers will get identical amount of reward.
- Random: The claimers will get a random amount of reward (the range should be from 0.00000001 to (2.0 * the available amount / the available capacity - 0.00000001)). To simplify user interaction, the implementation of this mode is a bit naive and someone might get a higher reward by using “Try & Abort”, so please use it for fun only.

Besides, we have a “Whitelist With Amount” mode, with which you can set a specific reward amount to each account on the whitelist manually.

## What’s NFT Raffle?

NFT Raffle is a way to airdrop NFTs. There are 4 main stages in an NFT Raffle:

- You create a new NFT Raffle by setting the basic information, depositing NFTs and setting the criteria for eligible accounts, then share the Raffle link to your community
- Community members go to the Raffle page, check their eligibility and register for the Raffle if they are eligible
- Once the registration end, you can draw the winners. For each draw, a winner will be selected randomly from registrants, and an NFT will be picked out randomly from NFTs in the Raffle as the reward for winner.
- Registrants go to the Raffle page to check whether they are winners or not, and claim the reward if they are.

To decide who is eligible for your Raffle, you can check **[here](#who-is-eligible)**.

## Who is eligible?

In Drizzle, you can decide who is eligible for your rewards by using our different modes.

- **[FLOAT Event](https://floats.city)**. You can limit the eligibility to people who own FLOATs of specific FLOAT Event at the time of the DROP or NFT Raffle being created.
- **[FLOAT Group](https://floats.city)**. You can also limit the eligibility to people who own FLOATs in a FLOAT Group. You can set a threshold to the number of FLOATs the users should have.
- Whitelist. You can upload a whitelist. Only accounts on the whitelist are eligible for rewards.

## Roadmap

More features (Merkle DROP, integration with DAO generators like **[Toucans](https://toucans.app)** etc) are to be added!

If you have any suggestion, or you want more tokens to be supported, feel free to contact us!