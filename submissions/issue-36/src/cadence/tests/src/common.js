import { getAccountAddress } from "@onflow/flow-js-testing";

const UFIX64_PRECISION = 8;

// UFix64 values shall be always passed as strings
export const toUFix64 = (value) => value.toFixed(UFIX64_PRECISION);

export const getGhostAdminAddress = async () => getAccountAddress("GhostAdmin");

export const getKittyAdminAddress = async () => getAccountAddress("KittyAdmin");
