import useSWR, { useSWRConfig } from 'swr';
import groupBy from 'lodash.groupby';
import omit from 'lodash.omit';
import axios from 'axios';
import { SoulMadeService } from '../services/SoulMadeService';
import { useAuthSession } from './useAuthSession';
import {
  findAllMainSaleData,
  findAllComponentSaleData,
  findAllDropMainSaleData,
  findAllDropComponentSaleData,
} from './graffleQuery';

const soulMadeService = SoulMadeService.getInstance();

export const useMainAssets = series => {
  return useSWR(series ? `useMainAssets/${series}` : null, () => {
    // return soulMadeService.getMainAssets({ series });
    //针对某个Series
    // return soulMadeService.getMainAssets_by_series_marketplace({ series });
    //暂时, 获取到所有销售的的main
    return soulMadeService.getMain_AllMarketPalce({ series });
  });
};

//MainDrop
export const useMainAssetsDrop = series => {
  return useSWR(`useMainAssetsDrop/${series}`, async () => {
    return findAllDropMainSaleData();
  });
};

export const useComponentAssets = series => {
  return useSWR(series ? `useComponentAssets/${series}` : null, () => {
    // return soulMadeService.getComponentAssets({ series });
    return soulMadeService.get_All_Components({ series });
  });
};

//ComponentDrop
export const useComponentAssetsDrop = series => {
  return useSWR(`useComponentAssetsDrop/${series}`, async () => {
    return findAllDropComponentSaleData();
  });
};

export const useBlockDropMainAssets = series => {
  const { data: assets, error } = useMainAssetsDrop(series);
  const seriesAssets = assets?.filter(x => x.blockEventData?.saleData?.mainDetail?.series);
  return { data: seriesAssets?.map(x => x.blockEventData), loading: !assets && !error };
};

export const useBlockDropComponentAssets = series => {
  const { data: assets, error } = useComponentAssetsDrop(series);
  const seriesAssets = assets?.filter(x => x.blockEventData?.saleData?.componentDetail?.series);
  return { data: seriesAssets?.map(x => x.blockEventData), loading: !assets && !error };
};

export const useBlockMarketplaceMainAssets = () => {
  return useSWR('useBlockMarketplaceMainAssets', async () => {
    return findAllMainSaleData();
  });
};

export const useBlockMarketplaceComponentAssets = () => {
  return useSWR('useBlockMarketplaceComponentAssets', async () => {
    return findAllComponentSaleData();
  });
};

export const useBlockDropMainProducts = series => {
  const { data: assets, error } = useMainAssetsDrop(series);
  const groupedAssets = groupBy(assets, 'blockEventData.saleData.mainDetail.ipfsHash');
  return {
    data: Object.values(groupedAssets)
      .map(x => x[0])
      .map(x => x.blockEventData),
    loading: !assets && !error,
  };
};

export const useBlockDropComponentProducts = series => {
  const { data: assets, error } = useComponentAssetsDrop(series);
  const groupedAssets = groupBy(assets, 'blockEventData.saleData.componentDetail.ipfsHash');
  return {
    data: Object.values(groupedAssets)
      .map(x => x[0])
      .map(x => x.blockEventData),
    loading: !assets && !error,
  };
};

export const useBlockMarketplaceMainProducts = () => {
  const { data: assets, error } = useBlockMarketplaceMainAssets();
  const groupedAssets = groupBy(assets, 'blockEventData.saleData.mainDetail.ipfsHash');
  return {
    data: Object.values(groupedAssets).map(x => x[0].blockEventData),
    loading: !assets && !error,
  };
};

export const useBlockMarketplaceComponentProducts = () => {
  const { data: assets, error } = useBlockMarketplaceComponentAssets();
  const groupedAssets = groupBy(assets, 'blockEventData.saleData.componentDetail.ipfsHash');
  return {
    data: Object.values(groupedAssets).map(x => x[0].blockEventData),
    loading: !assets && !error,
  };
};

// TODO: 这个貌似目前还是来自于链上？我们需要统一数据源
export const useAllAssets = series => {
  const { data: mainAssets, mainError } = useMainAssets(series);
  const { data: componentAssets, componentError } = useComponentAssets(series);
  const error = mainError || componentError;

  return {
    data: mainAssets && componentAssets ? [...mainAssets, ...componentAssets] : undefined,
    loading: !mainAssets && !componentAssets && !error,
  };
};

export const useAssetByIdType = (id, type) => {
  return useSWR(id && type ? `useAssetByIdType/${id}/${type}` : null, () => {
    return soulMadeService.getAssetByIdAndType({ id: Number(id), nftType: type });
  });
};

export const useUserAssets = () => {
  const { mutate } = useSWRConfig();
  const user = useAuthSession();
  const service = SoulMadeService.getInstance();
  const { data, error } = useSWR(
    () => `useUserAssets/${user.addr}`,
    async () => {
      return await service.getUserAssets(user.addr);
    },
  );
  return { data, error, loading: !data && !error, mutate: () => mutate(`useUserAssets/${user.addr}`) };
};

export const useUserAssetsByAddress = address => {
  const { mutate } = useSWRConfig();

  const service = SoulMadeService.getInstance();
  const { data, error } = useSWR(
    () => `useUserAssetsByAddress/${address}`,
    async () => {
      return await service.getUserAssets(address);
    },
  );
  return { data, error, loading: !data && !error, mutate: () => mutate(`useUserAssetsByAddress/${address}`) };
};

export const useUserAsset = (id, type) => {
  const { data, error, loading } = useUserAssets();
  const asset = data?.find(asset => asset.id === Number(id) && asset.nftType === type);
  return { data: asset, error, loading };
};

export const useGraffleSale = () => {
  const { data, error } = useSWR('useGraffleSale', async () => {
    return await axios.get(
      'https://prod-test-net-dashboard-api.azurewebsites.net/api/company/4b5000d7-7f39-4a34-8dba-19a97e26fd58/search?eventType=A.b4187e54e0ed55a8.SoulMadeMarketplace.SoulMadeForSale',
    );
  });
  return { data: data?.data.map(x => x.blockEventData.saleData), error, loading: !data && !error };
};

export const useGraffleForMainSale = () => {
  const { data, error } = useSWR('useGraffleForMainSale', async () => {
    return findAllMainSaleData();
  });
  return { data: data?.map(x => x.blockEventData.saleData), error, loading: !data && !error };
};

export const useGraffleForComponentSale = () => {
  const { data, error } = useSWR('useGraffleForComponentSale', async () => {
    return findAllComponentSaleData();
  });
  return { data: data?.map(x => x.blockEventData.saleData), error, loading: !data && !error };
};

export const useUserBalance = addr => {
  const { data, error } = useSWR(addr ? 'useUserBalance' : null, async () => {
    return await soulMadeService.getUserBalance(addr);
  });
  return { data, error, loading: !data && !error };
};

export const useUserSales = addr => {
  const { data, error } = useSWR(addr ? `useUserSales/${addr}` : null, async () => {
    return await soulMadeService.getUserSales(addr);
  });
  return { data, error, loading: !data && !error };
};

export const useCheckInit = addr => {
  const { data, error } = useSWR(addr ? 'useCheckInit' : null, async () => {
    return await soulMadeService.getInitStatus(addr);
  });
  return { data, error, loading: !data && !error };
};
