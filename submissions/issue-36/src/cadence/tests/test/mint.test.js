import path from "path";

import {
  emulator,
  init,
  getAccountAddress,
  shallPass,
  shallResolve,
  shallRevert,
  deployContractByName,
  getContractAddress,
  mintFlow
} from "@onflow/flow-js-testing";

import { getGhostAdminAddress, toUFix64 } from "../src/common";

import {
  deployWeaponItems,
  getWeaponItemCount,
  getWeaponItemSupply,
  mintWeaponItem,
  setupWeaponItemsOnAccount,
  transferWeaponItem,
} from "../src/weapon-items";

import { setupGnftAccount, setupFlowAccount, mintGnft, transferGnft, getGnftBalance } from "../src/tokens";

describe("mint-test", () => {
  beforeEach(async () => {
    const basePath = path.resolve(__dirname, "../../");
    await init(basePath);
    await emulator.start();
    return await new Promise((r) => setTimeout(r, 1000));
  });

  // Stop emulator, so it could be restarted
  afterEach(async () => {
    await emulator.stop();
    return await new Promise((r) => setTimeout(r, 1000));
  });

  it("free-mint-gnft", async () => {
    const GhostAdmin = await getGhostAdminAddress();
    await deployContractByName({ to: GhostAdmin, name: "FungibleToken" });
    await deployContractByName({ to: GhostAdmin, name: "GnftToken" });

    const Alice = await getAccountAddress("Alice");
    await setupGnftAccount(Alice);
    await mintGnft(Alice, 100.0);
    const balance = await getGnftBalance(Alice);
    expect(Number(balance)).toBe(100.0);
  });

  it("free-mint-weapon-item", async () => {
    const Alice = await getAccountAddress("Alice");
    await deployWeaponItems();
    await setupWeaponItemsOnAccount(Alice);

    await mintWeaponItem(Alice, "weapon item 0", 1, 0, "http://a0.png");
    const total = await getWeaponItemSupply();
    expect(Number(total)).toBe(1);
  });
});