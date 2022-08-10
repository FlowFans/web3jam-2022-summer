import React, { useState } from 'react';
import { Button, Dialog, DialogContent } from '@mui/material';
import Countdown from 'react-countdown';
import { EffectCarousel } from '../../../../common/Carousel';
import { SoulMadeService } from '../../../../services/SoulMadeService';
import Image from 'next/image';
import { useRouter } from 'next/router';
import styles from './MysteryBoxCarousel.module.scss';
import { LoadingGif } from '../../../../common/LoadingGif/LoadingGif';
import { useUserAssets } from '../../../../hooks/queries';
import { LoadingButton } from '@mui/lab';
import { StatusProgress } from '../../../../common/StatusProgress/StatusProgress';
import { useAuthSession } from '../../../../hooks/useAuthSession';

const soulMadeService = SoulMadeService.getInstance();

const BoxDialog = ({ assetId, open, onClose }) => {
  const router = useRouter();

  const { data: userAssets = [] } = useUserAssets();
  const asset = userAssets.find(x => x.id === assetId);

  const assetSeries = asset?.series || asset?.mainDetail?.series;

  const user = useAuthSession();

  const handleClick = () => {
    router.push(
      `profile/product/${assetSeries}/${asset.mainDetail?.ipfsHash}/${assetId}?nftType=SoulMadeMain&name=${asset.mainDetail?.name}`,
    );
  };

  return (
    <Dialog open={open} onClose={onClose}>
      <DialogContent className={styles.dialogContent}>
        <div className={styles.imgWrapper}>
          {/*todo(Guisong): update the image and text here */}
          <Image priority src="/new-design/images/antihuman_pack.png" layout="fill" alt="image" />
        </div>

        <div className={styles.title}>CONGRATULATIONS!</div>
        <div className={styles.msg}>You receive the items!</div>

        {/*todo(Guisong): use the simple version instead for now*/}
        {/*<Button onClick={handleClick}>Play now</Button>*/}
        <Button href={`\\profile\\${user?.addr}`}>Check it now!</Button>
      </DialogContent>
    </Dialog>
  );
};

export const MysteryBoxCarousel = ({ onSwiper, timeUp }) => {
  const [transaction, setTransaction] = useState();
  const [buyLoading, setBuyLoading] = useState(false);
  const [purchasedAssetId, setPurchasedAssetId] = React.useState(null);

  const carouselItems = [
    { img: '/new-design/images/omnist_1.png', title: '1 element inside!', price: 'FLOW  2.0', series: 'Omnist' },
    {
      img: '/new-design/images/antihuman_pack.png',
      title: '100 Limited!',
      price: 'FLOW  58.0',
      series: 'AntiHuman-Demons',
    },
    { img: '/new-design/images/omnist_10.png', title: '10 elements inside!', price: 'FLOW  10.0', series: 'Omnist10' },
  ];
  const [activeIndex, setActiveIndex] = useState(1);
  const activeSeries = carouselItems[activeIndex].series;

  const handleSwiper = idx => {
    setActiveIndex(idx);
    onSwiper(idx);
  };

  const handleBuy = async () => {
    try {
      setBuyLoading(true);
      const res = await soulMadeService.buyPack(activeSeries);
      res.subscribe(t => {
        setTransaction(t);
        if (t.errorMessage) {
          setBuyLoading(false);
        } else {
          if (t.status === 4) {
            const event = t.events.find(
              event => event.type.includes('SoulMadeComponent.Deposit') || event.type.includes('SoulMadeMain.Deposit'),
            );
            setPurchasedAssetId(event.data.id);
            setBuyLoading(false);
            setTransaction(undefined);
          }
        }
      });
    } catch (error) {
      console.error(error);
      setBuyLoading(false);
    } finally {
    }
  };

  return (
    <div className={styles.mysteryBoxCarousel}>
      {/*todo: shall we replace this gif? */}
      {buyLoading ? <LoadingGif src="/gifs/antihuman-pack-animation.gif" /> : null}
      {transaction ? <StatusProgress transaction={transaction} /> : null}
      <div className={styles.wrapper}>
        <EffectCarousel activeIndex={activeIndex} items={carouselItems} onSwiper={handleSwiper} />
        <div className={styles.btnWrapper}>
          {timeUp && activeIndex === 1 ? (
            <LoadingButton className={styles.btn} disabled>
              Sold Out
            </LoadingButton>
          ) : (
            <LoadingButton className={styles.btn} loading={buyLoading} onClick={handleBuy}>
              shop and open
            </LoadingButton>
          )}
        </div>
      </div>

      <BoxDialog
        assetId={purchasedAssetId}
        open={Boolean(purchasedAssetId)}
        onClose={() => setPurchasedAssetId(null)}
      />
    </div>
  );
};
