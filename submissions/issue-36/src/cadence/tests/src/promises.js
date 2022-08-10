import {
  mintFlow,
  executeScript,
  sendTransaction,
  deployContractByName,
  getContractAddress,
  getAccountAddress,
} from "@onflow/flow-js-testing";

import { getGhostAdminAddress, toUFix64 } from "./common";

export const deployPromises = async () => {
  const GhostAdmin = await getGhostAdminAddress();
  const AppVault = await getAccountAddress("AppVault");

  await deployContractByName({ to: GhostAdmin, name: "FungibleToken" });
  await deployContractByName({ to: GhostAdmin, name: "FlowToken" });
  await deployContractByName({ to: GhostAdmin, name: "GnftToken" });
  await deployContractByName({ to: GhostAdmin, name: "NonFungibleToken" });
  await deployContractByName({ to: GhostAdmin, name: "MetadataViews" });
  await deployContractByName({ to: GhostAdmin, name: "KittyItems" });

  const name = "TestPromises";
  // const args = ["a"];

  const [res, err] = await deployContractByName({
    GhostAdmin,
    name,
    // args,
  });

  console.log({ res }, { err });
  const addr = await getContractAddress("TestPromises");
  console.log("rental contract: ", addr);
};
