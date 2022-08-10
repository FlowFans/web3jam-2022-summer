import Image from 'next/image';
import cx from 'classnames';
import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';
import { Button } from '../../common/Button/Button';
import { SoulMadeService } from '../../services/SoulMadeService';
import { StatusProgress } from '../../common/StatusProgress/StatusProgress';
import { useAuthSession } from '../../hooks/useAuthSession';
import styles from './Header.module.scss';
import { Dialog, Divider, IconButton, ListItemIcon, Menu, MenuItem, Snackbar, useMediaQuery } from '@mui/material';
import { Logout, CollectionsOutlined, ContentCopy, PersonOutline, Menu as MenuIcon } from '@mui/icons-material';
import { useUserBalance, useCheckInit } from '../../hooks/queries';

const ColorMap = {
  '/': '#262626',
  '/drops': '#fff',
  '/mystery-box': '#fff',
};

const ButtonClassMap = {
  // '/': styles.dark,
  '/drops': styles.light,
  '/mystery-box': styles.light,
};

const soulMadeService = SoulMadeService.getInstance();

async function copyTextToClipboard(text) {
  if ('clipboard' in navigator) {
    return await navigator.clipboard.writeText(text);
  } else {
    return document.execCommand('copy', true, text);
  }
}

export const Header = () => {
  const isDesktop = useMediaQuery('(min-width:1024px)');
  const { pathname } = useRouter();
  const colorCode = ColorMap[pathname];
  const buttonClass = ButtonClassMap[pathname];
  const user = useAuthSession();
  const { data: flowAmount } = useUserBalance(user?.addr);

  const router = useRouter();
  const { data: initStatus, error: initError } = useCheckInit(user?.addr);

  const [loginPopupOpen, setLoginPopupOpen] = useState(false);

  const [linkActive, setLinkActive] = useState({
    drops: false,
    'mystery-box': false,
    marketplace: false,
  });

  useEffect(() => {
    const page = pathname.split('/')[1];
    setLinkActive({
      drops: page === 'drops',
      'mystery-box': page === 'mystery-box',
      marketplace: page === 'marketplace',
    });
  }, [pathname]);

  const [loginLoading, setLoginLoading] = useState(false);
  const [anchorElMobileMenu, setAnchorElMobileMenu] = useState(null);
  const [anchorElUserMenu, setAnchorElUserMenu] = useState(null);
  const [copySuccessSnackbarOpen, setCopySuccessSnackbarOpen] = useState(false);
  const [initLoading, setinitLoading] = useState(false);
  const [transaction, setTransaction] = useState();

  const brandLogoSize = isDesktop ? 52 : 32;

  const handleLogin = async () => {
    setLoginLoading(true);
    const res = await soulMadeService.login();
    handleMobileMenuClose();
    setLoginLoading(false);
    if (res.loggedIn) {
      setLoginPopupOpen(true);
    }
    if (await soulMadeService.getInitStatus(res.addr)) {
      try {
        const res = await soulMadeService.initAccount();
        res.subscribe(t => {
          setTransaction(t);
          if (t.errorMessage) {
            setinitLoading(false);
          } else {
            if (t.status === 4) {
              setinitLoading(false);
              setTransaction(undefined);
            }
          }
        });
      } catch (error) {
        setinitLoading(false);
      } finally {
        setLoginPopupOpen(false);
      }
    }
    // if(){
    //   console
    // }
  };

  const handleLogout = () => {
    soulMadeService.logout();
    router.replace('/');
    handleMobileMenuClose();
  };

  const handleMobileMenuOpen = event => {
    setAnchorElMobileMenu(event.currentTarget);
  };

  const handleMobileMenuClose = () => {
    setAnchorElMobileMenu(null);
    setAnchorElUserMenu(null);
  };

  const handleUserMenuOpen = event => {
    setAnchorElUserMenu(event.currentTarget);
  };

  const handleUserMenuClose = () => {
    setAnchorElUserMenu(null);
  };

  const handleUserMenuSelect = pathname => () => {
    router.push(pathname);
    handleUserMenuClose();
  };

  const handleMobileMenuClick = pathname => () => {
    router.push(pathname);
    handleMobileMenuClose();
  };

  const handleCopyAddr = async () => {
    copyTextToClipboard(user?.addr)
      .then(() => {
        setCopySuccessSnackbarOpen(true);
      })
      .catch(err => {
        console.log(err);
      });
  };

  const handleInit = async () => {
    setinitLoading(true);
    try {
      const res = await soulMadeService.initAccount();
      res.subscribe(t => {
        setTransaction(t);
        if (t.errorMessage) {
          setinitLoading(false);
        } else {
          if (t.status === 4) {
            setinitLoading(false);
            setTransaction(undefined);
          }
        }
      });
    } catch (error) {
      setinitLoading(false);
    } finally {
      setLoginPopupOpen(false);
    }
  };

  const loginUserIcon = (
    <IconButton size="large" onClick={handleUserMenuOpen} color="inherit">
      <PersonOutline className={styles.icon} />
    </IconButton>
  );

  const userMenu = (
    <>
      <Menu
        id="user-menu"
        anchorEl={anchorElUserMenu}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'right',
        }}
        keepMounted
        transformOrigin={{
          vertical: 'top',
          horizontal: 'right',
        }}
        open={Boolean(anchorElUserMenu)}
        onClose={handleUserMenuClose}
        PaperProps={{
          elevation: 0,
          sx: {
            overflow: 'visible',
            filter: 'drop-shadow(0px 2px 8px rgba(0,0,0,0.32))',
            mt: 1.5,
            '& .MuiAvatar-root': {
              width: 32,
              height: 32,
              ml: -0.5,
              mr: 1,
            },
            '&:before': {
              content: '""',
              display: 'block',
              position: 'absolute',
              top: 0,
              right: isDesktop ? 29 : 19,
              width: 10,
              height: 10,
              bgcolor: 'background.paper',
              transform: 'translateY(-50%) rotate(45deg)',
              zIndex: 0,
            },
          },
        }}
      >
        <MenuItem onClick={handleCopyAddr}>
          <ListItemIcon>
            <ContentCopy fontSize="small" />
          </ListItemIcon>
          <div style={{ fontWeight: 600 }}>{user?.addr}</div>
        </MenuItem>

        <Divider />

        <MenuItem>
          <Image src="/new-design/images/flow-logo.png" width={32} height={32} alt="flow logo" />
          <div style={{ marginLeft: 16 }}>
            <div style={{ color: 'rgb(135 135 135)' }}>Flow Balance</div>
            <div style={{ fontWeight: 600 }}>{flowAmount}</div>
          </div>
        </MenuItem>

        <Divider />

        <MenuItem onClick={handleUserMenuSelect(`/profile`)}>
          <ListItemIcon>
            <Image priority src="/images/my_collection_icon.png" alt="collectionLogo" width="20%" height="20%" />
            {/* <CollectionsOutlined fontSize="small" /> */}
          </ListItemIcon>
          My collections
        </MenuItem>

        <MenuItem onClick={handleUserMenuSelect(`/profile/marketplace`)}>
          <ListItemIcon>
            <Image priority src="/images/my_marketplace_icon.png" alt="marketplaceLogo" width="20%" height="20%" />
            {/* <CollectionsOutlined fontSize="small" /> */}
          </ListItemIcon>
          My sales
        </MenuItem>

        <Divider />

        <MenuItem onClick={handleLogout}>
          <ListItemIcon>
            <Logout fontSize="small" />
          </ListItemIcon>
          Logout
        </MenuItem>
      </Menu>
    </>
  );

  const brandLogo = (
    <Link href="/">
      <a style={{ display: 'flex', alignItems: 'center' }}>
        <Image priority src="/images/logo_bi@2x.png" alt="logo" width={brandLogoSize} height={brandLogoSize} />
        <div className={styles.logoText}>SoulMade</div>
      </a>
    </Link>
  );

  const desktopMenu = (
    <div className={styles.desktopMenu}>
      <div className={styles.brand}>{brandLogo}</div>
      <div className={styles.links}>
        <div className={styles.navs}>
          <div className={cx(styles.link, { [styles.active]: linkActive.drops })} key={Math.random()}>
            <Link href="/drops">Drops</Link>
          </div>
          <div className={cx(styles.link, { [styles.active]: linkActive['mystery-box'] })} key={Math.random()}>
            <Link href="/mystery-box">Mystery Box</Link>
          </div>
          <div className={cx(styles.link, { [styles.active]: linkActive.marketplace })} key={Math.random()}>
            <Link href="/marketplace">Marketplace</Link>
          </div>
        </div>

        <div className={styles.user}>
          {user && user.loggedIn ? (
            loginUserIcon
          ) : (
            <Button loading={loginLoading} size="sm" onClick={handleLogin} className={cx(styles.btn, buttonClass)}>
              Connect Wallet
            </Button>
          )}
        </div>
      </div>
    </div>
  );

  const mobileMenu = (
    <div className={styles.mobileMenu}>
      <IconButton size="large" onClick={handleMobileMenuOpen} color="inherit">
        <MenuIcon className={styles.icon} />
      </IconButton>
      <Menu
        anchorEl={anchorElMobileMenu}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'right',
        }}
        keepMounted
        transformOrigin={{
          vertical: 'top',
          horizontal: 'right',
        }}
        open={Boolean(anchorElMobileMenu)}
        onClose={handleMobileMenuClose}
      >
        <MenuItem onClick={handleMobileMenuClick('/drops')}>Drops</MenuItem>
        <MenuItem onClick={handleMobileMenuClick('/mystery-box')}>Mystery Box</MenuItem>
        <MenuItem onClick={handleMobileMenuClick('/marketplace')}>Marketplace</MenuItem>

        {user && user.loggedIn ? (
          <MenuItem onClick={handleLogout}>Logout</MenuItem>
        ) : (
          <MenuItem onClick={handleLogin}>Login</MenuItem>
        )}
      </Menu>

      <div className={styles.brand}>{brandLogo}</div>
      <div className={styles.user}>{user && user.loggedIn ? loginUserIcon : null}</div>
    </div>
  );
  return (
    <div className={styles.header} style={{ color: colorCode }}>
      {userMenu}
      <Snackbar
        anchorOrigin={{ vertical: 'top', horizontal: 'right' }}
        open={copySuccessSnackbarOpen}
        onClose={() => setCopySuccessSnackbarOpen(false)}
        message="Copied Address!"
      />
      <Dialog
        open={Boolean(loginPopupOpen && initStatus)}
        onClose={() => setLoginPopupOpen(false)}
        maxWidth="md"
        fullWidth
        PaperProps={{
          classes: {
            root: styles.loginPopup,
          },
        }}
      >
        <Image src="/new-design/images/login_popup.jpeg" alt="test" layout="fill" objectFit="cover" />
        {/* <Button onClick={handleInit}>Init Account</Button> */}
      </Dialog>
      {transaction ? <StatusProgress transaction={transaction} /> : null}

      <div className={styles.content}>{isDesktop ? desktopMenu : mobileMenu}</div>
    </div>
  );
};
