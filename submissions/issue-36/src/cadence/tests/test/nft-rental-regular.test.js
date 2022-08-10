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
  mintFlow,
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

import {
  deployNftRentalRegular,
  getOneRentInfo,
  listForRent,
  rentFrom,
} from "../src/nft-rental-regular";

import {
  setupGnftAccount,
  mintFlow1,
  mintGnft,
  getFlowBalance,
} from "../src/tokens";

// Increase timeout if your tests failing due to timeout
jest.setTimeout(10000);

describe("nft-rental-regular", () => {
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

  it("rent info after list", async () => {
    const Alice = await getAccountAddress("Alice");
    const Bob = await getAccountAddress("Bob");
    const aaa = await deployNftRentalRegular();
    console.log("rental deploy: ", aaa);
    await shallPass(setupWeaponItemsOnAccount(Alice));
    await setupGnftAccount(Alice);
    await mintGnft(Alice, 2000.0);
    await mintWeaponItem(Alice, "weapon item 0", 1, 0, "http://a0.png");
    await mintFlow(Alice, 10000.0);
    await mintFlow(Bob, 20000.0);

    var balance = await getFlowBalance(Bob);
    console.log("balance0: ", balance);
    const tokenId = 0;
    const endTime = 1669428215.0;
    const rentFee = 50.0;
    const guarantee = 100.0;
    await shallPass(listForRent(tokenId, endTime, rentFee, guarantee));
    await shallResolve(getOneRentInfo(tokenId));

    await rentFrom(0, "Bob", 51.0);
    const [r2, e2] = await shallResolve(getOneRentInfo(tokenId));
    console.log(r2, e2);
    var balance = await getFlowBalance(Bob);
    console.log("balance1: ", balance);
  });
});
