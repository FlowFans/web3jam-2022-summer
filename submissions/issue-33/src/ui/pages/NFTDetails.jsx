import React, { useState, useEffect } from 'react'
import { useParams } from 'react-router-dom'

import * as fcl from "@onflow/fcl";
import * as t from "@onflow/types";
import { getSaleNFTsScript } from "../cadence/scripts/get_sale_nfts";
import { purchaseTx } from "../cadence/transactions/purchase";

import './nftdetails.css'
import img1 from '../assets/A01.gif'
import img2 from '../assets/A02.gif'
import img3 from '../assets/A03.gif'
import img4 from '../assets/A04.gif'
import fileimg from '../assets/filesvg.jpg'
import priceimg from '../assets/pricesvg.jpg'
import itemimg from '../assets/itemsvg.jpg'
import detailimg from '../assets/detailimg.jpg'
import flowlogo from '../assets/flow.jpg'

export default function NFTDetails() {
  var img;
  const params = useParams()
  const [nfts, setNFTs] = useState([]);
  switch (params) {
    case 1:
      img = { img1}
      break;
    case 2:
      img = { img2 }
      break;
    case 3:
      img = { img3 }
      break;
    case 4:
      img = { img4 }
      break;

  }
  debugger
  useEffect(() => {
    window.scrollTo(0, 0);
    getUserSaleNFTs();
  }, []);


  const getUserSaleNFTs = async () => {
    const result = await fcl.send([
      fcl.script(getSaleNFTsScript),
      fcl.args([
        fcl.arg('0xf2011014fb9bee77', t.Address),
      ])
    ]).then(fcl.decode);

    console.log(result);
    setNFTs(result);
  }

  const purchase = async (id) => {
    const transactionId = await fcl.send([
      fcl.transaction(purchaseTx),
      fcl.args([
        fcl.arg('0xf2011014fb9bee77', t.Address),
        fcl.arg(id, t.UInt64)
      ]),
      fcl.payer(fcl.authz),
      fcl.proposer(fcl.authz),
      fcl.authorizations([fcl.authz]),
      fcl.limit(9999)
    ]).then(fcl.decode);

    console.log(transactionId);
    return fcl.tx(transactionId).onceSealed();
  }

  return (
    <div className='nftbgcolor' >
      <div className='nftContainer'>
        {Object.keys(nfts).map(nftid => (
          <div key={nftid}>
            <div className='nftMain'>

              <div className='nftsec1'>
                <div className='nftCard'>
                  <div>
                    <div className='nftCardImg'>
                      <div className='nftImage__aAWBQ'>
                        <div className='cardImg__x_WlD'>
                          <span className='nftImgSpan'>
                            <span className='nftTopSpan'></span>
                            <img className='nftimg1' src={img} alt="token" decoding="async" />
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div className='nftPrice'>
                    <div className='priceHead'>
                      <div>
                        <img src={priceimg} />&nbsp;&nbsp;&nbsp;Current price
                      </div>
                    </div>

                    <div>
                      <div className='nft_currentPrice'>
                        <div className='price_d1'>
                          <div className='price_d2'>
                            <img className='flowlogo' src={flowlogo} />
                            <span className='nft_price'>{nfts[nftid].price}</span>
                            <span className='nft_price_usd'>$168.32</span>
                          </div>
                          <button className='price_button'>
                            <div className='price_button_div' onClick={() => purchase(nftid)}>
                              Buy now
                            </div>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>

                <div className='nftinfo'>
                  <div className='infoHeader'>
                    <div className='infoHeaderTitle'>
                      <span>Bethel China</span>
                    </div>
                  </div>

                  <div className='nftName'>
                    IRIS&nbsp;NFT
                  </div>

                  <div className='nftOwnInfo'>
                    <div className='nftOwner'>
                      Owned by&nbsp;:&nbsp;
                      <span className='ownerAdd'>0xf2011014fb9bee77</span> <br /><br />
                      Collection&nbsp;:&nbsp;
                      <span className='ownerAdd'>Irisation Singles</span>
                    </div>
                  </div>



                  <div className='nftdes'>
                    <div className='nft_frameHeader'>
                      <div>
                        <img src={fileimg} /> &nbsp;&nbsp;&nbsp;Description
                      </div>
                    </div>
                    <div>
                      <div className='nft_description'>
                        <div>
                          <span className='descripArticle'>
                            People often perceive the world directly through their eyes.
                            There is a group of people who see the world differently from many others.
                            This is a unique gift given to them by God and should not be a barrier to life.
                            Perhaps their lives are not all black but they already have a line drawing and they need our help to fill it with colour.
                            <br />We intend to use the visually impaired as our first NFT project,
                            using NFT's potential for transparency and variety of play to make the public aware of the urgency of eye disease and get involved in the charity to help visually impaired children.
                          </span>
                        </div>

                      </div>
                    </div>
                  </div>

                  <div className='nft_div_details'>
                    <div className='nft_div_details_top'>
                      <div>
                        <img className='nft_div_detailimg' src={detailimg} />
                        &nbsp;&nbsp;&nbsp;Details
                      </div>
                    </div>
                    <div>
                      <div className='nft_div_details_bot'>
                        <div className='nft_div_details_li'>
                          <span>Contract Address</span>
                          <span className='nft_div_detail_add'>0xf2011014fb9bee77</span>
                        </div>
                        <div className='nft_div_details_li'>
                          <span>Token ID</span>
                          <span className='nft_div_detail_add'>#{nftid}</span>
                        </div>
                        <div className='nft_div_details_li'>
                          <span>Token Standard</span>
                          <span className='nft_div_detail_add'>FLOW-NFT</span>
                        </div>
                        <div className='nft_div_details_li'>
                          <span>Blockchain</span>
                          <span className='nft_div_detail_add'>FLOW</span>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              <div className='nftsec2'>
                <div className='bft_foldingFrame'>

                  <div className='nft_frameHeader'>
                    <div>
                      <img src={itemimg} />&nbsp;&nbsp;&nbsp;Item Activity
                    </div>
                  </div>

                  <div>
                    <div className='nft_activity'>
                      <div className='table_container'>
                        <table className='Table_table__shulN'>
                          <tbody>
                            <tr>
                              <th height="63" className='act_th' width="89px">Event</th>
                              <th height="63" className='act_th' width="269px">Item</th>
                              <th height="63" className='act_th' width="77px">Price</th>
                              <th height="63" className='act_th' width="155px">From</th>
                              <th height="63" className='act_th' width="155px">To</th>
                              <th height="63" className='act_th' width="197px">Transaction</th>
                            </tr>
                            <tr>
                              <td><div className='td_d1'>Minted</div></td>
                              <td><div className='td_d2'><img src={img2} className='tableimg' />&nbsp;&nbsp;&nbsp;Manuscript</div></td>
                              <td><span className='td_d3'>—</span></td>
                              <td><div className='td_d4'>0x00...000000</div></td>
                              <td><div className='td_d4'>0x00...c7f4ff</div></td>
                              <td><div className='td_d4'>0xa0...cc56d1</div></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

            </div>
          </div>
        ))}

      </div>
    </div>


  )
}
