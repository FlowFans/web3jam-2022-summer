# Daybreak

## Track

- [x] NFT x DAO/Tools
- [ ] NFT x Game/Entertainment
- [ ] NFT x Life/Metaverse

## Description
Flow Blockchain Analytics Platform

### Problem statement
There is no such platform or tool that user can use to analyse Flow blockchain.

Target audience would be:

- Flow dApp builders: so that they can build their own dashboards to track their dApps
- Users who are interested in analysis on Flow blockchain

Evidence for the need:

- The need has already been proven by Dune(the analytics platform for Ethereum). If there is such huge needs in Ethereum, so is Flow.
- Me as a Flow dApp developer also need such tool in order to do some analysis on our own application. If there is not such generic platform, we would have to build it on our own.

### Proposed solution

- Product Introduction
A datawarehouse that stores all event data of Flow blockchain.
An analytics platform that users can use to analyse Flow blockchain and visualize in whatever ways they want.

- Technical architecture
Data collection: Flow Badger DB
Data warehouse: AWS Redshift
Data exploration and visualization platform: Superset

## What was done during Web3 Jam

We experimented 3 different ways of collecting event data of Flow blockchain.

1. Flow-DPS service: It turned out to be malfunctioning.
2. Directly read from chain: very time consuming
3. Use ProtocolDBArchive and extract from there: this is solution we will be using.

Then we implemented a series of jobs that extract data from ProtocolDBArchive, then parse and reformat it so it could be loaded to our data warehouse.

We further built our cloud infrastructure on AWS, we built our dataw arehouse and VMs for running our platform.

And we configured Superset, the data exploration and visualization platform, so it can be integrated with our data warehouse.

- Running Prototype: [Daybreak](http://daybreak.so:8088/)

Username: admin
Password: admin

- [Demo Video](https://drive.google.com/file/d/1uSGulDFuRXP6ZJec18sF87zfsEkfoM7T/view?usp=sharing) 
