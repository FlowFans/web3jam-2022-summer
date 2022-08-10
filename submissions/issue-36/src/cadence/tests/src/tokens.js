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

export const setupGnftAccount = async (account) => {
  const name = "../transactions/tokens/setup_gnft_account";
  const signers = [account];

  const [res, err] = await sendTransaction({ name, signers});
  // console.log("setup gnft account res: ", res, err);
};

export const setupFlowAccount = async (signer) => {
  const name = "../transactions/tokens/setup_flow_account";
  const signers = [signer];

  const [res, err] = await sendTransaction({ name, signers });
  // console.log("setup flow account res: ", res, err);
};

export const mintGnft = async (to, amount) => {
  const name = "../transactions/tokens/mint_gnft";
  const args = [to, amount];
  const signers = [to];

  const [res, err] = await sendTransaction({ name, args, signers });
  console.log("mint gnft res: ", res, err);
};

export const transferGnft = async (amount, to) => {
  const GhostAdmin = await getGhostAdminAddress();
  const name = "../transactions/tokens/transfer_gnft";
  const args = [amount, to];
  const signers = [GhostAdmin];

  const [res, err] = await sendTransaction({ name, args, signers });
  // console.log("transfer gnft res: ", res, err);
};

export const mintFlow1 = async (to, amount) => {
  const GhostAdmin = await getGhostAdminAddress();
  const name = "../transactions/tokens/mint_flow";
  const args = [to, amount];
  const signers = [GhostAdmin];

  return sendTransaction({ name, args, signers });
  // console.log("mint flow res: ", res, err);
};

export const transferFlow1 = async (amount, to) => {
  const GhostAdmin = await getGhostAdminAddress();
  const name = "../transactions/tokens/transfer_flow";
  const args = [amount, to];
  const signers = [GhostAdmin];

  const [res, err] = await sendTransaction({ name, args, signers });
  // console.log("transfer flow res: ", res, err);
};

export const getGnftBalance = async (account) => {
  const name = "../scripts/tokens/get_gnft_balance";
  const args = [account];
  const signers = [account];

  const [res, err] = await executeScript({ name, args, signers });
  // console.log("get GNFT balance res: ", res, err);
  return res
}

export const getFlowBalance = async (account) => {
  const name = "../scripts/tokens/get_flow_balance";
  const args = [account];
  const signers = [account];

  return executeScript({ name, args, signers });
}