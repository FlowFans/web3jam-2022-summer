import { Button, useMediaQuery, Dialog, DialogContent, DialogActions } from '@mui/material';
import React, { useState } from 'react';
import { LoadingButton } from '@mui/lab';
import cx from 'classnames';
import styles from './Introduction.module.scss';
import { LazyImage } from '../../../../common/LazyImage';
import { SoulMadeService } from '../../../../services/SoulMadeService';
import { StatusProgress } from '../../../../common/StatusProgress/StatusProgress';
import { LoadingGif } from '../../../../common/LoadingGif/LoadingGif';
import Image from 'next/image';
import { useAuthSession } from '../../../../hooks/useAuthSession';

const soulMadeService = SoulMadeService.getInstance();

const BoxDialog = ({ open, onClose }) => {
  const user = useAuthSession();
  return (
    <Dialog open={open} onClose={onClose}>
      <DialogContent className={styles.dialogContent}>
        <div className={styles.imgWrapper}>
          {/*todo(Guisong): the image here and also the on in the pack, should be updated*/}
          <Image priority src="/new-design/images/antihuman_pack.png" layout="fill" alt="image" />
        </div>
        <div className={styles.title}>CONGRATULATIONS</div>
        <div className={styles.msg}>You had claimed items!</div>
        <Button href={`\\profile\\${user?.addr}`}>Check it now!</Button>
      </DialogContent>
    </Dialog>
  );
};

const ClaimConfirmDialog = ({ open, onClose, claimed, onClaim, claimLoading }) => {
  return (
    <Dialog open={open} onClose={onClose}>
      <DialogContent className={styles.dialogContent}>
        {/*{claimed*/}
        {/*  ? 'You have already claimed! Please visit MysteryBox and Drops to get more'*/}
        {/*  : 'Welcome! You have one free claim!'}*/}
        {/*todo(Guisong): all has been claimed*/}
        {claimed
          ? 'You have already claimed! Please visit MysteryBox and Drops to get more'
          : 'All have been claimed! There will be another round coming soon, please join our Discord and stay tuned!'}
      </DialogContent>
      <DialogActions>
        <LoadingButton loading={claimLoading} disabled={true} className={styles.btnforclaim} onClick={onClaim}>
          {/*<LoadingButton loading={claimLoading} disabled={claimed} className={styles.btnforclaim} onClick={onClaim}>*/}
          Claim
        </LoadingButton>
        <Button variant="outlined" onClick={onClose}>
          Close
        </Button>
      </DialogActions>
    </Dialog>
  );
};

const MobileCategorySection = ({ className, imgSrc, title, subtitle, desc, actions }) => {
  return (
    <div className={cx(styles.categorySection, className)}>
      <div className={styles.subtitle}>{subtitle}</div>

      <div className={styles.content}>
        <div className={styles.title}>{title}</div>
        <div className={styles.desc}>{desc}</div>
        <div className={styles.image}>
          <LazyImage asset={imgSrc} />
        </div>
        <div className={styles.actions}>
          {actions.map((action, idx) => (
            <div key={idx} className={styles.action}>
              {action}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

const DesktopCategorySection = ({ className, imgSrc, title, subtitle, desc, actions, reverse = false }) => {
  const desktopImg = (
    <div className={cx(styles.img, styles.item)}>
      <LazyImage asset={imgSrc} />
    </div>
  );

  const desktopContent = (
    <div className={cx(styles.content, styles.item)}>
      <div className={styles.category}>{subtitle}</div>
      <div className={styles.title}>{title}</div>
      <div className={styles.desc}>{desc}</div>
      {actions && (
        <div className={styles.actions}>
          {actions.map((action, idx) => (
            <div key={idx} className={styles.action}>
              {action}
            </div>
          ))}
        </div>
      )}
    </div>
  );

  return (
    <div className={styles.card}>
      {reverse ? (
        <>
          {desktopImg}
          {desktopContent}
        </>
      ) : (
        <>
          {desktopContent}
          {desktopImg}
        </>
      )}
    </div>
  );
};

export const Introduction = () => {
  const isDesktop = useMediaQuery('(min-width: 1024px)');

  const [transaction, setTransaction] = useState();
  const [checkClaimedLoading, setCheckClaimedLoading] = useState(false);
  const [claimLoading, setClaimLoading] = useState(false);
  const [claimConfirmOpen, setClaimConfirmOpen] = useState(false);
  const [claimed, setClaimed] = useState(false);
  const [claimSuccessOpen, setClaimSuccessOpen] = useState(false);

  // todo(Guisong): is this correct?
  const user = useAuthSession();

  const handleCheckFreeClaimed = async () => {
    try {
      setCheckClaimedLoading(true);
      // todo(Guisong): address here
      // todo: it returns true, if he has already claimed.
      const res = await soulMadeService.checkFreeClaimed(user?.addr);
      setClaimed(res);
      setCheckClaimedLoading(false);
      setClaimConfirmOpen(true);
      // res.subscribe(t => {
      //   setTransaction(t);
      //   if (t.errorMessage) {
      //     setBuyLoading(false);
      //   } else {
      //     if (t.status === 4) {
      //       setBuyLoading(false);
      //       setTransaction(undefined);
      //       setClaimed(true);
      //     }
      //   }
      // });
    } catch (error) {
      setCheckClaimedLoading(false);
      console.log(error);
    }
  };

  const handleFreeClaim = async () => {
    try {
      setClaimLoading(true);
      setClaimConfirmOpen(false);
      const res = await soulMadeService.freeClaim();
      res.subscribe(t => {
        setTransaction(t);
        if (t.errorMessage) {
          setClaimLoading(false);
        } else {
          if (t.status === 4) {
            setClaimLoading(false);
            setTransaction(undefined);
            setClaimSuccessOpen(true);
          }
        }
      });
    } catch (error) {
      setClaimLoading(false);
      setClaimSuccessOpen(false);
      console.log(error);
    }
  };

  return (
    <div className={styles.introduction}>
      <ClaimConfirmDialog
        open={claimConfirmOpen}
        onClose={() => setClaimConfirmOpen(false)}
        claimed={claimed}
        onClaim={handleFreeClaim}
        claimLoading={claimLoading}
      />

      {transaction ? <StatusProgress transaction={transaction} /> : null}
      {claimLoading ? <LoadingGif src="/gifs/mysterybox-loading.gif" /> : null}
      <BoxDialog open={claimSuccessOpen} onClose={() => setClaimSuccessOpen(false)} />

      <div className={styles.section}>
        <div className={styles.image}>
          <LazyImage asset="/new-design/images/sushi.png" />
          {/* <Image src="/new-design/images/sushi.png" alt="sushi" objectFit="contain" layout="fill" /> */}
        </div>

        <div className={styles.content}>
          <div className={styles.title}>
            A new way to make your own{' '}
            <span style={{ fontFamily: "'Bradley Hand Bold', cursive", fontWeight: 500, fontSize: 50 }}>N</span>
            <span style={{ fontFamily: "'Nanum Brush Script', cursive", fontWeight: 500, fontSize: 50 }}>F</span>
            <span style={{ fontFamily: "'Party LET Plain'", fontWeight: 500, fontSize: 50 }}>T</span> art
          </div>
          <div className={styles.desc}>{`It's time to unleash the composability of NFTs`}</div>
          <div className={styles.actions}>
            <LoadingButton
              className={styles.btnforclaim}
              loading={checkClaimedLoading}
              onClick={handleCheckFreeClaimed}
            >
              Free Claim
            </LoadingButton>
            <Button className={styles.btn} href="/drops">
              EXPLORE DROPS
            </Button>
            <Button className={styles.btn} href="/mystery-box">
              Open mystery box
            </Button>
          </div>
        </div>
      </div>

      <div className={styles.freeMintImage}>
        <div style={{ position: 'relative', width: '100%', aspectRatio: '128 / 75' }}>
          <LazyImage asset="/images/omnist-banner.png" />
        </div>
      </div>

      {!isDesktop ? (
        <>
          <MobileCategorySection
            imgSrc="/gifs/home-page-drops.gif"
            title="Buy different NFT pieces and put them together in your own way!"
            subtitle="Drops"
            desc="It's time to unleash the composability of NFTs"
            actions={[
              <Button key="try-now" className={styles.btn} href="/drops">
                TRY NOW
              </Button>,
            ]}
          />
          <MobileCategorySection
            imgSrc="/gifs/home-page-mystery-box.gif"
            title="Draw a set and see what surprise you will get."
            subtitle="Mystery Box"
            actions={[
              <Button key="try-now" className={styles.btn} href="/mystery-box">
                TRY NOW
              </Button>,
            ]}
          />
          <MobileCategorySection
            imgSrc="/gifs/home-page-marketplace.gif"
            title="You are the creator, you are the seller. Let's make your creation profitable!"
            subtitle="MarketPlace"
            actions={[
              <Button key="try-now" className={styles.btn} href="/marketplace">
                TRY NOW
              </Button>,
            ]}
          />
        </>
      ) : (
        <>
          <DesktopCategorySection
            imgSrc="/gifs/home-page-drops.gif"
            title="Buy different NFT pieces and put them together in your own way!"
            subtitle="Drops"
            desc="It's time to unleash the composability of NFTs"
            actions={[
              <Button key="try-now" className={styles.btn} href="/drops">
                TRY NOW
              </Button>,
            ]}
          />
          <DesktopCategorySection
            reverse={true}
            imgSrc="/gifs/home-page-mystery-box.gif"
            title="Draw a set and see what surprise you will get."
            subtitle="Mystery Box"
            actions={[
              <Button key="try-now" className={styles.btn} href="/mystery-box">
                TRY NOW
              </Button>,
            ]}
          />
          <DesktopCategorySection
            imgSrc="/gifs/home-page-marketplace.gif"
            title="You are the creator, you are the seller. Let's make your creation profitable!"
            subtitle="MarketPlace"
            actions={[
              <Button key="try-now" className={styles.btn} href="/marketplace">
                TRY NOW
              </Button>,
            ]}
          />
        </>
      )}

      <div className={styles.videoWrapper}>
        <iframe
          className={styles.video}
          width="100%"
          height="100%"
          src="https://www.youtube.com/embed/q57JJs8zcpo?controls=0"
          title="YouTube video player"
          frameBorder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowFullScreen
        ></iframe>
      </div>
    </div>
  );
};
