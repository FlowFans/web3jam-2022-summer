import Head from 'next/head'
import { useState, useEffect } from "react";
import "../config";
import * as fcl from "@onflow/fcl";

export default function Home() {

  // Transaction status, 4 is sealed
  const [transactionStatus, setTransactionStatus] = useState(null)

  // User info
  const [user, setUser] = useState({ loggedIn: null })
  useEffect(() => fcl.currentUser.subscribe(setUser), [])

  // Profile info
  const [name, setName] = useState('')
  const [classification, setClassification] = useState()

  // Balance info
  const [lnt_balance, setLntBalance] = useState('')
  const [lnc_balance, setLncBalance] = useState(0)
  const [material_count, setMaterialCount] = useState(1)
  
  // NFT info
  const [nft_count, setCount] = useState('')
  
  var target_addr = ""
  if (user.addr == "0x3de9c43a330b0332") {
    target_addr = "0x92b5f54adc7cec22"
  } else {
    target_addr = "0x3de9c43a330b0332"
  }

  fcl.config().put("0xaccount", user.addr)

  // =========================
  // Profile
  // =========================

  // Profile (script): To sync profile from chain 
  const syncProfile = async () => {
    const profile = await fcl.query({
      cadence: `
        import Profile from 0xaccount

        pub fun main(address: Address): Profile.ReadOnly? {
          return Profile.read(address)
        }
      `,
      args: (arg, t) => [arg(user.addr, t.Address)]
    })
    setName(profile?.name ?? 'No Profile')
    setClassification(profile?.classification ?? 'No Profile')
  }

  // Profile (transaction): To init Profile for the account
  const initProfile = async () => {
    const trans = await fcl.mutate({
      cadence: `
        import Profile from 0xaccount

        transaction {
          prepare(account: AuthAccount) {
            if (!Profile.check(account.address)) {
              account.save(<- Profile.new(), to: Profile.privatePath)
              account.link<&Profile.Base{Profile.Public}>(Profile.publicPath, target: Profile.privatePath)
            }
          }
        }
      `,
      payer: fcl.authz,
      proposer: fcl.authz,
      authorizations: [fcl.authz],
      limit: 50
    })
    fcl.tx(trans).subscribe(res => setTransactionStatus(res.status))
  }

  // Profile (transaction): To change name and classification
  const changeProfile = async () => {
    const trans = await fcl.mutate({
      cadence: `
        import Profile from 0xaccount

        transaction() {
          prepare(account: AuthAccount) {
            account
              .borrow<&Profile.Base{Profile.Owner}>(from: Profile.privatePath)!
              .setName("Zhixiny2")
            account
              .borrow<&Profile.Base{Profile.Owner}>(from: Profile.privatePath)!
              .setClassification("Teacher")
          }
        }
      `,
      payer: fcl.authz,
      proposer: fcl.authz,
      authorizations: [fcl.authz],
      limit: 50
    })
    fcl.tx(trans).subscribe(res => setTransactionStatus(res.status))
    setLncBalance(0)
    setMaterialCount(1)
  }

  // Profile (transaction): To switch the classification to `Learner`
  const switch2Learner = async () => {
    const trans = await fcl.mutate({
      cadence: `
        import Profile from 0xaccount

        transaction() {
          prepare(account: AuthAccount) {
            account
              .borrow<&Profile.Base{Profile.Owner}>(from: Profile.privatePath)!
              .setClassification("Learner")
          }
        }
      `,
      payer: fcl.authz,
      proposer: fcl.authz,
      authorizations: [fcl.authz],
      limit: 50
    })
    fcl.tx(trans).subscribe(res => setTransactionStatus(res.status))
    setLncBalance(0)
    setMaterialCount(1)
  }

  // Profile (transaction): To switch the classification to `Teacher`
  const switch2Teacher = async () => {
    const trans = await fcl.mutate({
      cadence: `
        import Profile from 0xaccount

        transaction() {
          prepare(account: AuthAccount) {
            account
              .borrow<&Profile.Base{Profile.Owner}>(from: Profile.privatePath)!
              .setClassification("Teacher")
          }
        }
      `,
      payer: fcl.authz,
      proposer: fcl.authz,
      authorizations: [fcl.authz],
      limit: 50
    })
    fcl.tx(trans).subscribe(res => setTransactionStatus(res.status))
    setLncBalance(0)
    setMaterialCount(1)
  }

  // =========================
  // LNT Balance
  // =========================

  // Balance (script): To get the balance of the account
  const syncBalance = async () => {
    const balance = await fcl.query({
      cadence: `
      import FungibleToken from 0xcontracts
      import ExampleToken from 0xaccount

      pub fun main(account: Address): UFix64 {
          let acct = getAccount(account)
          let vaultRef = acct.getCapability(ExampleToken.BalancePublicPath)
              .borrow<&ExampleToken.Vault{FungibleToken.Balance}>()
              ?? panic("Could not borrow Balance reference to the Vault")

          return vaultRef.balance
      }
      `,
      args: (arg, t) => [arg(user.addr, t.Address)]
    })
    setLntBalance(balance ?? '')
  }

  // Balance (transaction): To init Balance(ExampleToken) for the account
  const initBalance = async () => {
    const transfer_trans = await fcl.mutate({
      cadence: `
      import FungibleToken from 0xcontracts
      import ExampleToken from 0xaccount

      transaction {
          prepare(signer: AuthAccount) {
              signer.save(
                  <-ExampleToken.createEmptyVault(),
                  to: ExampleToken.VaultStoragePath
              )
              signer.link<&ExampleToken.Vault{FungibleToken.Receiver}>(
                  ExampleToken.ReceiverPublicPath,
                  target: ExampleToken.VaultStoragePath
              )
              signer.link<&ExampleToken.Vault{FungibleToken.Balance}>(
                  ExampleToken.BalancePublicPath,
                  target: ExampleToken.VaultStoragePath
              )
          }
      }
      `,
      payer: fcl.authz,
      proposer: fcl.authz,
      authorizations: [fcl.authz],
      limit: 50
    })
    fcl.tx(transfer_trans).subscribe(res => setTransactionStatus(res.status))
  }

  // Balance (transaction): To transfer the ExampleToken from the account to others
  const transferBalance = async () => {
    const transfer_trans = await fcl.mutate({
      cadence: `
      import FungibleToken from 0xcontracts
      import ExampleToken from 0xaccount

      transaction(amount: UFix64, to: Address) {
          let sentVault: @FungibleToken.Vault
          prepare(signer: AuthAccount) {
              let vaultRef = signer.borrow<&ExampleToken.Vault>(from: ExampleToken.VaultStoragePath)
              self.sentVault <- vaultRef.withdraw(amount: amount)
          }

          execute {
              let recipient = getAccount(to)
              let receiverRef = recipient.getCapability(ExampleToken.ReceiverPublicPath)
                  .borrow<&{FungibleToken.Receiver}>()
              ?? panic("Could not borrow receiver reference to the recipient's Vault")
              receiverRef.deposit(from: <-self.sentVault)
          }
      }
      `,
      args: (arg, t) => [arg(10.0, t.UFix64), arg(target_addr, t.Address)],
      payer: fcl.authz,
      proposer: fcl.authz,
      authorizations: [fcl.authz],
      limit: 50
    })
    fcl.tx(transfer_trans).subscribe(res => setTransactionStatus(res.status))
  }

  // =========================
  // NFT
  // =========================

  // NFT (script): To get the count of NFT
  const getCount = async () => {
    const nft = await fcl.query({
      cadence: `
      import ExampleNFT from 0xaccount

      pub fun main(): UInt64 {
          return ExampleNFT.totalSupply
      }
      `
    })
    setCount(nft ?? 'No NFT')
  }

  // NFT (transaction): To mint NFT
  const mintNFT = async () => {
    const transfer_trans = await fcl.mutate({
      cadence: `
      import NonFungibleToken from 0xcontracts
      import FungibleToken from 0xcontracts
      import ExampleNFT from 0xaccount

      transaction(
          recipient: Address,
          name: String,
          description: String,
          thumbnail: String,
      ) {
          let minter: &ExampleNFT.NFTMinter
          let recipientCollectionRef: &{NonFungibleToken.CollectionPublic}
          let mintingIDBefore: UInt64

          prepare(signer: AuthAccount) {
              self.mintingIDBefore = ExampleNFT.totalSupply
              self.minter = signer.borrow<&ExampleNFT.NFTMinter>(from: ExampleNFT.MinterStoragePath)
                  ?? panic("Account does not store an object at the specified path")
              self.recipientCollectionRef = getAccount(recipient)
                  .getCapability(ExampleNFT.CollectionPublicPath)
                  .borrow<&{NonFungibleToken.CollectionPublic}>()
                  ?? panic("Could not get receiver reference to the NFT Collection")
          }
          post {
            self.recipientCollectionRef.getIDs().contains(self.mintingIDBefore): "The next NFT ID should have been minted and delivered"
            ExampleNFT.totalSupply == self.mintingIDBefore + 1: "The total supply should have been increased by 1"
          }
          execute {
              self.minter.mintNFT(
                  recipient: self.recipientCollectionRef,
                  name: name,
                  description: description,
                  thumbnail: thumbnail,
              )
          }
      }
      `,
      args: (arg, t) => [arg(user.addr, t.Address), 
                         arg("Example NFT 0", t.String),
                         arg("This is an example NFT", t.String),
                         arg("example.jpg", t.String)],
      payer: fcl.authz,
      proposer: fcl.authz,
      authorizations: [fcl.authz],
      limit: 50
    })
    fcl.tx(transfer_trans).subscribe(res => setTransactionStatus(res.status))
    setLncBalance(lnc_balance - 10)
  }

  // =========================
  // Pages
  // =========================

  // Add LNC for learner after learning
  const LearnerEarn = async () => {
    setLncBalance(lnc_balance + 10)
  }

  // Add LNC for teacher after teaching
  const TeacherEarn = async () => {
    setLncBalance(lnc_balance + 10)
    setMaterialCount(material_count + 1)
  }

  // The teaching material example
  const Material = () => {
    return (
      <div>
        <h5>Announcing the First Bit Hotel NFT Wave Sale: Wave 1 — Hotel</h5>
        <p>It is time.</p>
        <p>
            Bit Hotel is releasing our Founder Sale and we’re dropping some of the most scarce and good-looking NFTs to get things started.
        </p>
        <p>
            Up to Legendary Rarity Hotel Guests will go on sale at in October and can be whitelisted for today! Follow our socials to be the first to hear when the drop is launched and get notified if you got whitelisted.
        </p>
        <p>
            Do note that we cannot share a specific date until right before the drop in order to prevent malicious botters to snatch more than they should. We’ll make sure to prevent these sneaky bots from entering the drop as best we can using anti-bot measures.
        </p>
        <p>
            This is your first chance to buy NFTs which will play a critical role in the Bit Hotel metaverse. All NFTs have a capped supply and will never be available again!
        </p>
        <p>   
            Buy early to take advantage of our early INO prices, have a slightly higher chance of snatching low serial numbers, and earning lucrative in-game rewards.
        </p>
        <p>   
            Make sure to add the Binance Smartchain to your metamask prior to entering the sale or whitelisting!
        </p>
      </div>
    )
  }

  // simulate the action to add teaching material
  const DuplicatePage = () => {
    var m = [];
    for (var i = 0; i < material_count; i++) {
      m.push(<Material />);
    }
    return m
  }

  // Learner Page
  const LearnerPage = () => {
    return (
      <div style={{"border-top": "2px solid #000"}}>
        <Material />
        <div>
          <button onClick={LearnerEarn}>Learn!</button>
        </div>
      </div>
    )
  }

  // Teacher Page
  const TeacherPage = () => {
    return (
      <div style={{"border-top": "2px solid #000"}}>
        <DuplicatePage />
        <div>
          <button onClick={TeacherEarn}>Teach!</button>
        </div>
      </div>
    )
  }

  // The page after log-in
  const AuthedState = () => {
    return (
      <div>
        <div>Transaction Status: {transactionStatus ?? "--"}</div>

        <div>
          <h4>Profile</h4>
          <div>Address: {user.addr ?? ""}</div>
          <div>Name: {name ?? ""}</div>
          <div>Classification: {classification ?? ""}</div>
          <div>
            <button onClick={syncProfile}>Sync Profile</button>
            <button onClick={initProfile}>init Profile</button>
            <button onClick={changeProfile}>Change Profile</button>
            <button onClick={switch2Learner}>Switch to Learner</button>
            <button onClick={switch2Teacher}>Switch to Teacher</button>
          </div>

          <h4>Balance</h4>
          <div>LNT: {lnt_balance ?? ""}</div>
          <div>LNC: {lnc_balance}</div>
          <div>
            <button onClick={syncBalance}>Sync LNT Balance</button>
            <button onClick={initBalance}>Init Balance</button>
            <button onClick={transferBalance}>Transfer LNT</button>
          </div>

          <h4>NFT</h4>
          <div>Count: {nft_count ?? "0"}</div>
          <div>
            <button onClick={getCount}>Get Count</button>
            <button onClick={mintNFT}>Mint NFT</button>
          </div>

          <h4> </h4>
        </div>
        <div><button onClick={fcl.unauthenticate}>Log Out</button></div>
        <div>
          {classification == "Learner"
            ? <LearnerPage />
            : <TeacherPage />
          }
        </div>
      </div>
    )
  }

  // The page before log-in
  const UnauthenticatedState = () => {
    return (
      <div>
        <button onClick={fcl.logIn}>Log In</button>
        <button onClick={fcl.signUp}>Sign Up</button>
      </div>
    )
  }

  return (
    <div>
      <Head>
        <title>LearntVerse Demo</title>
        <meta name="description" content="LearntVerse" />
      </Head>
      <h1>LearntVerse</h1>
      <div>
        {user.loggedIn
          ? <AuthedState />
          : <UnauthenticatedState />
        }
      </div>
    </div>
  );
}
