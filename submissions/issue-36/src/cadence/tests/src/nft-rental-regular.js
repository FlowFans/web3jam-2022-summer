import path from "path";

import {
  mintFlow,
  executeScript,
  sendTransaction,
  deployContractByName,
  getContractAddress,
  getAccountAddress,
} from "@onflow/flow-js-testing";

import { getGhostAdminAddress, toUFix64 } from "./common";

import {
  setupGnftAccount,
  setupFlowAccount,
  mintGnft,
  mintFlow1,
  transferGnft,
  transferFlow1,
} from "./tokens";

export const deployNftRentalRegular = async () => {
  const GhostAdmin = await getGhostAdminAddress();
  const AppWallet = await getAccountAddress("AppWallet");
  const platformWallet = await getAccountAddress("paltformWallet");

  await deployContractByName({ to: GhostAdmin, name: "FungibleToken" });
  await deployContractByName({ to: GhostAdmin, name: "FlowToken" });
  await deployContractByName({ to: GhostAdmin, name: "GnftToken" });
  await deployContractByName({ to: GhostAdmin, name: "NonFungibleToken" });
  await deployContractByName({ to: GhostAdmin, name: "MetadataViews" });
  await deployContractByName({ to: GhostAdmin, name: "WeaponItems" });
  console.log("GhostAdmin: ", GhostAdmin);

  const name = "NFTRentalRegular";
  const nftName = "nft";
  const appName = "app";
  const platformFeeRate = 0.01;
  const minRentPeriod = 3600.0;
  const guarantee = 100.0;
  const claimerPercent = 0.2;
  const appPercent = 0.4;

  const args = [
    platformFeeRate,
    minRentPeriod,
    guarantee,
    claimerPercent,
    appPercent,
    AppWallet,
    platformWallet,
    nftName,
    appName,
  ];
  const [res, err] = await deployContractByName({
    GhostAdmin,
    name,
    args,
  });

  // console.log({ res }, { err });
  // const addr = await getContractAddress("NFTRentalRegular");
  // console.log("rental contract: ", addr);
};

export const listForRent = async (tokenId, endTime, rentFee, guarantee) => {
  const Alice = await getAccountAddress("Alice");
  const name = "NFTRentalRegular/list_for_rent";
  const args = [tokenId, endTime, rentFee, guarantee];
  const signers = [Alice];

  await setupGnftAccount(Alice);
  await setupFlowAccount(Alice);
  await mintFlow(Alice, 1000.0);
  await mintGnft(Alice, 1000.0);

  // const [res, err] = await sendTransaction({ name, args, signers });
  // console.log("listForRent res: ", res, err);
  return sendTransaction({ name, args, signers });
};

export const rentFrom = async (tokenId, userName, fee) => {
  const user = await getAccountAddress(userName);
  const bob = await getAccountAddress("Bob");
  console.log("user: ", user);
  console.log("bob: ", bob);

  const name = "NFTRentalRegular/rent_from";
  const args = [tokenId, fee];
  const signers = [user];

  return sendTransaction({ name, args, signers });
}

export const getOneRentInfo = async (tokenId) => {
  const Alice = await getAccountAddress("Alice");

  const name = "NFTRentalRegular/get_one_rent_info";
  const args = [tokenId];
  const signers = [Alice];

  return executeScript({ name, args, signers });
};
