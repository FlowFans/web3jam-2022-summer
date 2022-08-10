import Head from 'next/head'
import "../../next.js UI/flow/config";
import { useState, useEffect } from "react";
import * as fcl from "@onflow/fcl";
import IndexNavbar from "../components/Navbars/IndexNavbar";
import Link from "next/link";
import Footer from "../components/Footers/Footer";
import React from "react";

export default function Index() {

    const [user, setUser] = useState({loggedIn: null})
    const [name, setName] = useState('')
    const [transactionStatus, setTransactionStatus] = useState(null) // NEW

    useEffect(() => fcl.currentUser.subscribe(setUser), [])

    const sendQuery = async () => {
        const profile = await fcl.query({
            cadence: `
        import Profile from 0xProfile

        pub fun main(address: Address): Profile.ReadOnly? {
          return Profile.read(address)
        }
      `,
            args: (arg, t) => [arg(user.addr, t.Address)]
        })

        setName(profile?.name ?? 'No Profile')
    }

    const initAccount = async () => {
        const transactionId = await fcl.mutate({
            cadence: `
        import Profile from 0xProfile

        transaction {
          prepare(account: AuthAccount) {
            // Only initialize the account if it hasn't already been initialized
            if (!Profile.check(account.address)) {
              // This creates and stores the profile in the user's account
              account.save(<- Profile.new(), to: Profile.privatePath)

              // This creates the public capability that lets applications read the profile's info
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

        const transaction = await fcl.tx(transactionId).onceSealed()
        console.log(transaction)
    }

    // NEW
    const executeTransaction = async () => {
        const transactionId = await fcl.mutate({
            cadence: `
        import Profile from 0xProfile

        transaction(name: String) {
          prepare(account: AuthAccount) {
            account
              .borrow<&Profile.Base{Profile.Owner}>(from: Profile.privatePath)!
              .setName(name)
          }
        }
      `,
            args: (arg, t) => [arg("Flow Developer!", t.String)],
            payer: fcl.authz,
            proposer: fcl.authz,
            authorizations: [fcl.authz],
            limit: 50
        })

        fcl.tx(transactionId).subscribe(res => setTransactionStatus(res.status))
    }

    const AuthedState = () => {
        return (
            <div>
                <div>Address: {user?.addr ?? "No Address"}</div>
                <div>Profile Name: {name ?? "--"}</div>
                <div>Transaction Status: {transactionStatus ?? "--"}</div> {/* NEW */}
                <button onClick={sendQuery}>Send Query</button>
                <button onClick={initAccount}>Init Account</button>
                <button onClick={executeTransaction}>Execute Transaction</button> {/* NEW */}
                <button onClick={fcl.unauthenticate}>Log Out</button>
            </div>
        )
    }

    const UnauthenticatedState = () => {
        return (
            <div>
                <button onClick={fcl.logIn}>Log In</button>
                <button onClick={fcl.signUp}>Sign Up</button>
            </div>
        )
    }

    return (
        <>
            <IndexNavbar fixed />
            <section className="header relative pt-16 items-center flex h-screen max-h-860-px">
                <div className="container mx-auto items-center flex flex-wrap">
                    <div className="w-full md:w-8/12 lg:w-6/12 xl:w-6/12 px-4">
                        <div className="pt-32 sm:pt-0">
                            <h2 className="font-semibold text-4xl text-blueGray-100">
                                TIK 8,<br/> Enjoy the Web3 Activities <br/> :)<br/>
                            </h2>

                            <div className="mt-12">
                                <a
                                    href="https://www.creative-tim.com/learning-lab/tailwind/nextjs/overview/notus?ref=nnjs-index"
                                    target="_blank"
                                    className="get-started text-white font-bold px-6 py-4 rounded outline-none focus:outline-none mr-1 mb-1 bg-blueGray-400 active:bg-blueGray-500 uppercase text-sm shadow hover:shadow-lg ease-linear transition-all duration-150" rel="noreferrer"
                                >
                                    购买
                                </a>
                                <a
                                    href="https://github.com/creativetimofficial/notus-nextjs?ref=nnjs-index"
                                    className="github-star ml-1 text-white font-bold px-6 py-4 rounded outline-none focus:outline-none mr-1 mb-1 bg-blueGray-700 active:bg-blueGray-600 uppercase text-sm shadow hover:shadow-lg"
                                    target="_blank" rel="noreferrer"
                                >
                                    换一批
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <Footer />
        </>
    )
}

