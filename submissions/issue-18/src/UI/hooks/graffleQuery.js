import { filter, replace, uniqBy, find } from 'lodash';
import moment from 'moment';

export const getGraffleQueryUrl = query => {
  const editeQuery = replace(query, 'CONTRACT', process.env.NEXT_PUBLIC_CONTRACT);
  // console.log("EDIT", editeQuery)
  return `${process.env.NEXT_PUBLIC_GRAFFLE_URL}${editeQuery}`;
};

const findItem = async (marketplace = true, main = true) => {
  var projectid = '';
  var contractaddress = '0x' + process.env.NEXT_PUBLIC_CONTRACT;
  var purchasedQuery = '';
  var withdrawnQuery = '';
  //get project id
  if (main) {
    projectid = process.env.NEXT_PUBLIC_MAIN_PROJECTID;
  } else {
    projectid = process.env.NEXT_PUBLIC_COMPONENT_PROJECTID;
  }
  //get sale data
  let saleQueryURL = getGraffleQueryUrl(
    `?eventType=A.CONTRACT.SoulMadeMarketplace.SoulMadeForSale&projectId=${projectid}`,
  );
  let saleData = await (await fetch(saleQueryURL)).json();
  if (marketplace) {
    saleData = saleData.filter(item => item.blockEventData.address != contractaddress);
  } else {
    saleData = saleData.filter(item => item.blockEventData.address == contractaddress);
  }
  let uniqSaleData = uniqBy(saleData, i => i.blockEventData.id);
  //get main or component
  if (main) {
    purchasedQuery = getGraffleQueryUrl('?eventType=A.CONTRACT.SoulMadeMarketplace.SoulMadeMainPurchased');
    withdrawnQuery = getGraffleQueryUrl('?eventType=A.CONTRACT.SoulMadeMarketplace.SoulMadeMainSaleWithdrawn');
  } else {
    purchasedQuery = getGraffleQueryUrl('?eventType=A.CONTRACT.SoulMadeMarketplace.SoulMadeComponentPurchased');
    withdrawnQuery = getGraffleQueryUrl('?eventType=A.CONTRACT.SoulMadeMarketplace.SoulMadeComponentSaleWithdrawn');
  }
  let purchasedData = await (await fetch(purchasedQuery)).json();
  let uniqpurchasedData = uniqBy(purchasedData, i => i.blockEventData.id);
  let withdrawnData = await (await fetch(withdrawnQuery)).json();
  let uniqWithDrawData = uniqBy(withdrawnData, i => i.blockEventData.tokenId);
  // start filter
  const filtered = filter(uniqSaleData, item => {
    const purchasedVersion = find(uniqpurchasedData, s => s.blockEventData.id === item.blockEventData.id);
    const withdrawnVersion = find(uniqWithDrawData, s => s.blockEventData.tokenId === item.blockEventData.id);
    if (!purchasedVersion && !withdrawnVersion) return true;
    if (!purchasedVersion) {
      return moment(item.eventDate).isAfter(moment(withdrawnVersion.eventDate));
    } else if (!withdrawnVersion) {
      return moment(item.eventDate).isAfter(moment(purchasedVersion.eventDate));
    } else {
      const mostRecentDate = moment(purchasedVersion.eventDate).isAfter(moment(withdrawnVersion.eventDate))
        ? moment(purchasedVersion.eventDate)
        : moment(withdrawnVersion.eventDate);
      return moment(item.eventDate).isAfter(mostRecentDate);
    }
  });
  // console.log(main ? 'main' : 'component', marketplace ? 'marketplace' : 'drop', filtered);

  return filtered;
};

export const findAllMainSaleData = async () => {
  return findItem(true, true);
};

export const findAllComponentSaleData = async () => {
  return findItem(true, false);
};

export const findAllDropMainSaleData = async () => {
  return findItem(false, true);
};

export const findAllDropComponentSaleData = async () => {
  return findItem(false, false);
};
