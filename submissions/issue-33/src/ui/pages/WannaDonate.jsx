import React from 'react'
import { Carousel } from 'antd';
import * as fcl from "@onflow/fcl";
import * as t from "@onflow/types";
import { useState, useEffect } from 'react';
import { getAuthorizeNFTsScript } from "../cadence/scripts/get_authorize_nfts";
import { getAuthorizeTx } from "../cadence/transactions/get_authorize";

import './nftdetails.css'
import './wannadonate.css'

import img2 from '../assets/1.png'
import fileimg from '../assets/filesvg.jpg'
import priceimg from '../assets/pricesvg.jpg'
import itemimg from '../assets/itemsvg.jpg'
import detailimg from '../assets/detailimg.jpg'
import flowlogo from '../assets/flow.jpg'
import bigimg from '../assets/donateimg.png'
import colcimg from '../assets/collection.jpg'
import A1 from '../assets/A01.gif'
import A2 from '../assets/A02.gif'
import A3 from '../assets/A03.gif'
import A4 from '../assets/A04.gif'
import bg1 from '../assets/layer/bg01.png'
import bg2 from '../assets/layer/bg02.png'
import bg3 from '../assets/layer/bg03.png'
import hd1 from '../assets/layer/head01.png'
import hd2 from '../assets/layer/head02.png'
import hd3 from '../assets/layer/head03.png'
import sd1 from '../assets/layer/shoulder01.png'
import sd2 from '../assets/layer/shoulder02.png'
import sd3 from '../assets/layer/shoulder03.png'
import c1 from '../assets/color1.png'
import c2 from '../assets/color2.png'
import c3 from '../assets/color3.png'
import c4 from '../assets/color4.png'
import show from '../assets/donateshow.png'


export default function WannaDonate() {
  const [nfts, setNFTs] = useState([]);

  useEffect(() => {
    getUserAuthorizeNFTs();
  }, [])

  const getUserAuthorizeNFTs = async () => {
    const result = await fcl.send([
      fcl.script(getAuthorizeNFTsScript),
      fcl.args([
        fcl.arg('0xf2011014fb9bee77', t.Address),
      ])
    ]).then(fcl.decode);

    console.log(result);
    setNFTs(result);
  }

  const getAuthorize = async (id) => {
    const transactionId = await fcl.send([
      fcl.transaction(getAuthorizeTx),
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
      <div className='donate_div_img'>
        <img className='donate_img' src={bigimg} />
      </div>
      <div className='donate_show'>
        <img className='donate_show_img' src={show} />
      </div>

      <div className='nftContainer'>
        {Object.keys(nfts).map(nftid => (
          <div key={nftid}>
            <div className='nftMain'>

              <div className='nftsec11'>
                <div className='nftCard'>
                  <div>
                    <div className='nftCardImg'>
                      <div className='nftImage__aAWBQ'>
                        <div className='cardImg__x_WlD'>
                          <span className='nftImgSpan'>
                            <span className='nftTopSpan'></span>
                            <img className='nftimg1' src={img2} alt="token" decoding="async" />
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div>
                    <div className='nftCardImg2'>
                      <div className='nftImage__aAWBQ'>
                        <div className='cardImg__x_WlD'>
                          {/* <Carousel autoplay> */}
                          <div>
                            <div className='wanna_slide'>
                              <Carousel autoplay>
                                <img className='wanna_slide_img' src={bg1} />
                                <img className='wanna_slide_img' src={bg2} />
                                <img className='wanna_slide_img' src={bg3} />
                              </Carousel>
                              <Carousel autoplay>
                                <img className='wanna_slide_img' src={hd1} />
                                <img className='wanna_slide_img' src={hd2} />
                                <img className='wanna_slide_img' src={hd3} />
                              </Carousel>
                            </div>
                            <div className='wanna_slide'>
                              <Carousel autoplay>
                                <img className='wanna_slide_img' src={sd1} />
                                <img className='wanna_slide_img' src={sd2} />
                                <img className='wanna_slide_img' src={sd3} />
                              </Carousel>
                            </div>
                          </div>
                          {/* <div>
                          <div className='wanna_slide'>
                            <img className='wanna_slide_img' src={hd1}/>
                            <img className='wanna_slide_img' src={hd2}/>
                          </div>
                          <div className='wanna_slide'>
                            <img className='wanna_slide_img' src={hd3}/>
                          </div>
                        </div>
                        <div>
                          <div className='wanna_slide'>
                            <img className='wanna_slide_img' src={sd1}/>
                            <img className='wanna_slide_img' src={sd2}/>
                          </div>
                          <div className='wanna_slide'>
                            <img className='wanna_slide_img' src={sd3}/>
                          </div>
                        </div> */}
                          {/* </Carousel> */}
                        </div>
                      </div>
                    </div>
                  </div>

                  <div className='nftPrice'>
                    <div className='priceHead'>
                      <div>
                        <img src={priceimg} />&nbsp;&nbsp;&nbsp;Single donation amount
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
                            <div className='price_button_div' onClick={() => getAuthorize(nftid)}>
                              Donate
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
                      <span className='ownerAdd'>Irisation Progams</span>
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
                            ‘He has low vision, but he has enough remaining vision to distinguish faces and write his favourite words.’
                            ‘She has had both eyes removed and replaced with prosthetic eyes due to cell tumours, but she is a warrior, singing and laughing freely.’
                            <br /><br />‘He is a totally blind child who loves to feel the world with hugs and music.’
                            ‘His enviable hair and spirit are enough to make people ignore his visual impairment and cerebral palsy.’
                            <br /><br />There are so many stories of children waiting to be heard here at Bathel China (爱百福). Our designer (Wu Di) has seen in these stories the interesting souls and the tenacity, just like the solid heads in our NFT. These children are independent and equal members of society, and they have diverse backgrounds and perspectives as well. We wanted to convey these messages with NFT's customisable background-and-colour-based playing format, personalising NFT to correspond to each unique child.
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
                              <td><div className='td_d1'>Donated</div></td>
                              <td><div className='td_d2'><img src={c4} className='tableimg' />&nbsp;&nbsp;&nbsp;Manuscript</div></td>
                              <td><span className='td_d3'>40</span></td>
                              <td><div className='td_d4'>Bethel China</div></td>
                              <td><div className='td_d4'>0xa0...er87r7</div></td>
                              <td><div className='td_d4'>0xe0...uu8d4t</div></td>
                            </tr>
                            <tr>
                              <td><div className='td_d1'>Donated</div></td>
                              <td><div className='td_d2'><img src={c3} className='tableimg' />&nbsp;&nbsp;&nbsp;Manuscript</div></td>
                              <td><span className='td_d3'>40</span></td>
                              <td><div className='td_d4'>Bethel China</div></td>
                              <td><div className='td_d4'>0xa0...er87r7</div></td>
                              <td><div className='td_d4'>0xe0...uu8d4t</div></td>
                            </tr>
                            <tr>
                              <td><div className='td_d1'>Donated</div></td>
                              <td><div className='td_d2'><img src={c2} className='tableimg' />&nbsp;&nbsp;&nbsp;Manuscript</div></td>
                              <td><span className='td_d3'>40</span></td>
                              <td><div className='td_d4'>Bethel China</div></td>
                              <td><div className='td_d4'>0xa0...er87r7</div></td>
                              <td><div className='td_d4'>0xe0...uu8d4t</div></td>
                            </tr>
                            <tr>
                              <td><div className='td_d1'>Donated</div></td>
                              <td><div className='td_d2'><img src={c1} className='tableimg' />&nbsp;&nbsp;&nbsp;Manuscript</div></td>
                              <td><span className='td_d3'>40</span></td>
                              <td><div className='td_d4'>Bethel China</div></td>
                              <td><div className='td_d4'>0xa0...er87r7</div></td>
                              <td><div className='td_d4'>0xe0...uu8d4t</div></td>
                            </tr>
                            <tr>
                              <td><div className='td_d1'>Listed</div></td>
                              <td><div className='td_d2'><img src={img2} className='tableimg' />&nbsp;&nbsp;&nbsp;Manuscript</div></td>
                              <td><span className='td_d3'>20</span></td>
                              <td><div className='td_d4'>Bethel China</div></td>
                              <td><div className='td_d4'>IRIS &nbsp;NFT</div></td>
                              <td><div className='td_d4'>0xa0...cc56d1</div></td>
                            </tr>
                            <tr>
                              <td><div className='td_d1'>Fragmented</div></td>
                              <td><div className='td_d2'><img src={img2} className='tableimg' />&nbsp;&nbsp;&nbsp;Manuscript</div></td>
                              <td><span className='td_d3'>30</span></td>
                              <td><div className='td_d4'>Bethel China</div></td>
                              <td><div className='td_d4'>IRIS &nbsp;NFT</div></td>
                              <td><div className='td_d4'>0xa0...cc56d1</div></td>
                            </tr>
                            <tr>
                              <td><div className='td_d1'>Minted</div></td>
                              <td><div className='td_d2'><img src={img2} className='tableimg' />&nbsp;&nbsp;&nbsp;Manuscript</div></td>
                              <td><span className='td_d3'>—</span></td>
                              <td><div className='td_d4'>0x00...000000</div></td>
                              <td><div className='td_d4'>IRIS &nbsp;NFT</div></td>
                              <td><div className='td_d4'>0xa0...cc56d1</div></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              <div className='nftsec3'>
                <div className='wanna_collection'>
                  <div className='wanna_collection_top'>
                    <div>
                      <img className='wanna_coll_img' src={colcimg} />&nbsp;&nbsp;&nbsp;More from this collection
                    </div>
                  </div>
                  <div className='wanna_collection_bto'>
                    <div className='wanna_collection_bto_d1'>
                      <img className='wanna_collection_bto_d1_img' src={A1} />
                      <div className='wanna_collection_bto_d1_txt1'>
                        Bethel China
                        <span className='coll_bto_d1_txt_logo'>
                          <img className='flowlogo2' src={flowlogo} />&nbsp; 80
                        </span>
                      </div>
                      <div className='wanna_collection_bto_d1_txt1'>
                        <div className='coll_bto_d1_txt_name'>IRIS NFT #01</div>
                        <div className='coll_bto_d1_txt_price'>$ 88.06</div>
                      </div>
                    </div>
                    <div className='wanna_collection_bto_d1'>
                      <img className='wanna_collection_bto_d1_img' src={A2} />
                      <div className='wanna_collection_bto_d1_txt1'>
                        Bethel China
                        <span className='coll_bto_d1_txt_logo'>
                          <img className='flowlogo2' src={flowlogo} />&nbsp; 80
                        </span>
                      </div>
                      <div className='wanna_collection_bto_d1_txt1'>
                        <div className='coll_bto_d1_txt_name'>IRIS NFT #02</div>
                        <div className='coll_bto_d1_txt_price'>$ 88.06</div>
                      </div>
                    </div>
                    <div className='wanna_collection_bto_d1'>
                      <img className='wanna_collection_bto_d1_img' src={A3} />
                      <div className='wanna_collection_bto_d1_txt1'>
                        Bethel China
                        <span className='coll_bto_d1_txt_logo'>
                          <img className='flowlogo2' src={flowlogo} />&nbsp; 80
                        </span>
                      </div>
                      <div className='wanna_collection_bto_d1_txt1'>
                        <div className='coll_bto_d1_txt_name'>IRIS NFT #03</div>
                        <div className='coll_bto_d1_txt_price'>$ 88.06</div>
                      </div>
                    </div>
                    <div className='wanna_collection_bto_d1'>
                      <img className='wanna_collection_bto_d1_img' src={A4} />
                      <div className='wanna_collection_bto_d1_txt1'>
                        Bethel China
                        <span className='coll_bto_d1_txt_logo'>
                          <img className='flowlogo2' src={flowlogo} />&nbsp; 80
                        </span>
                      </div>
                      <div className='wanna_collection_bto_d1_txt1'>
                        <div className='coll_bto_d1_txt_name'>IRIS NFT #04</div>
                        <div className='coll_bto_d1_txt_price'>$ 88.06</div>
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
