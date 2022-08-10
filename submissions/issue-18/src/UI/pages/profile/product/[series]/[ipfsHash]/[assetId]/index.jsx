import { useRouter } from 'next/router';
import React from 'react';
import { ProfileAssetScreen } from '../../../../../../components/ProfileAssetScreen/ProfileAssetScreen';
import { useUserAsset } from '../../../../../../hooks/queries';

const ProfileAssetPage = () => {
  const router = useRouter();
  const { assetId, nftType } = router.query;
  const { data: asset } = useUserAsset(assetId, nftType);

  return <ProfileAssetScreen asset={asset} />;
};

export default ProfileAssetPage;
