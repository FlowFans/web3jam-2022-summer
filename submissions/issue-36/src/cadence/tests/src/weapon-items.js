import {
  mintFlow,
  executeScript,
  sendTransaction,
  deployContractByName,
} from "@onflow/flow-js-testing";
import { getGhostAdminAddress } from "./common";

/*
 * Deploys NonFungibleToken and WeaponItems contracts to GhostAdmin.
 * @throws Will throw an error if transaction is reverted.
 * @returns {Promise<[{*} txResult, {error} error]>}
 * */
export const deployWeaponItems = async () => {
  const GhostAdmin = await getGhostAdminAddress();
  await deployContractByName({ to: GhostAdmin, name: "NonFungibleToken" });
  await deployContractByName({ to: GhostAdmin, name: "MetadataViews" });
  await deployContractByName({ to: GhostAdmin, name: "WeaponItems" });
};

/*
 * Setups WeaponItems collection on account and exposes public capability.
 * @param {string} account - account address
 * @returns {Promise<[{*} txResult, {error} error]>}
 * */
export const setupWeaponItemsOnAccount = async (account) => {
  const name = "../transactions/weaponItems/setup_account";
  const signers = [account];
  return sendTransaction({ name, signers });
};

/*
 * Returns WeaponItems supply.
 * @throws Will throw an error if execution will be halted
 * @returns {UInt64} - number of NFT minted so far
 * */
export const getWeaponItemSupply = async () => {
  const name = "../scripts/weaponItems/get_weapon_items_supply";
  const [res, err] =  await executeScript({ name });
  // console.log("getWeaponItemSupply", res, err);
  return res;
};

/*
 * Mints WeaponItem of a specific **itemType** and sends it to **recipient**.
 * @param {UInt64} itemType - type of NFT to mint
 * @param {string} recipient - recipient account address
 * @returns {Promise<[{*} result, {error} error]>}
 * */
export const mintWeaponItem = async (
  recipient,
  itemName,
  attack,
  defence,
  url
) => {
  const name = "../transactions/weaponItems/mint_weapon_item";
  const args = [recipient, itemName, attack, defence, url];
  const signers = [recipient];

  return sendTransaction({ name, args, signers });
  // console.log("mintWeaponItem", res, err);
};

/*
 * Transfers WeaponItem NFT with id equal **itemId** from **sender** account to **recipient**.
 * @param {string} sender - sender address
 * @param {string} recipient - recipient address
 * @param {UInt64} itemId - id of the item to transfer
 * @throws Will throw an error if execution will be halted
 * @returns {Promise<*>}
 * */
export const transferWeaponItem = async (sender, recipient, itemId) => {
  const name = "../transactions/weaponItems/transfer_weapon_item";
  const args = [recipient, itemId];
  const signers = [sender];

  const [res, err] = await sendTransaction({ name, args, signers });
  // console.log("transferWeaponItem", res, err);
};

/*
 * Returns the WeaponItem NFT with the provided **id** from an account collection.
 * @param {string} account - account address
 * @param {UInt64} itemID - NFT id
 * @throws Will throw an error if execution will be halted
 * @returns {UInt64}
 * */
export const getWeaponItem = async (account, itemID) => {
  const name = "../scripts/weaponItems/get_weapon_item";
  const args = [account, itemID];

  return await executeScript({ name, args });
};

/*
 * Returns the number of weapon Items in an account's collection.
 * @param {string} account - account address
 * @throws Will throw an error if execution will be halted
 * @returns {UInt64}
 * */
export const getWeaponItemCount = async (account) => {
  const name = "../sctipts/weaponItems/get_collection_length";
  const args = [account];

  return await executeScript({ name, args });
};
