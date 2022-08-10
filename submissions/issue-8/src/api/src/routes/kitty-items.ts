/**
 * Kitty Items Router
 * 
 * Router class that defines API REST endpoints used for minting and moving Kitty Items.
 * Endpoints call kitty-items service with request data.
 *
 */
import express, {Request, Response, Router} from "express"
import {body} from "express-validator"
import {validateRequest} from "../middlewares/validate-request"
import {KittyItemsService} from "../services/kitty-items"

function initKittyItemsRouter(kittyItemsService: KittyItemsService): Router {
  const router = express.Router()

  router.post(
    "/kitty-items/mint",
    [body("recipient").exists()],
    validateRequest,
    async (req: Request, res: Response) => {
      const {recipient} = req.body
      const tx = await kittyItemsService.mint(recipient)
      return res.send({
        transaction: tx,
      })
    }
  )

  router.post(
    "/kitty-items/sign-with-admin-minter",
    async (req: Request, res: Response) => {
      const { signable } = req.body
      console.log("signable:" + signable)
      const signature = kittyItemsService.signWithAdminMinter(signable.message)
      console.log("signature:" + signature)
      return res.send({
        signature,
      })
    }
  )

  router.post(
    "/kitty-items/add_minter",
    [body("recipient").exists()],
    validateRequest,
    async (req: Request, res: Response) => {
      const {recipient} = req.body
      const tx = await kittyItemsService.mint(recipient)
      return res.send({
        transaction: tx,
      })
    }
  )

  router.post(
    "/kitty-items/mint-and-list",
    [body("recipient").exists()],
    validateRequest,
    async (req: Request, res: Response) => {
      // const {recipient, key} = req.body
      // const tx = await kittyItemsService.add_minter(recipient, key)
      // return res.send({
      //   transaction: tx,
      // })
    }
  )

  router.post("/kitty-items/setup", async (req: Request, res: Response) => {
    const transaction = await kittyItemsService.setupAccount()
    return res.send({
      transaction,
    })
  })

  router.post(
    "/kitty-items/transfer",
    [body("recipient").exists(), body("itemID").isInt()],
    validateRequest,
    async (req: Request, res: Response) => {
      const {recipient, itemID} = req.body
      const tx = await kittyItemsService.transfer(recipient, itemID)
      return res.send({
        transaction: tx,
      })
    }
  )

  router.get(
    "/kitty-items/collection/:account",
    async (req: Request, res: Response) => {
      const collection = await kittyItemsService.getCollectionIds(
        req.params.account
      )
      return res.send({
        collection,
      })
    }
  )

  router.get(
    "/kitty-items/item/:address/:itemID",
    async (req: Request, res: Response) => {
      const item = await kittyItemsService.getKittyItem(
        parseInt(req.params.itemID),
        req.params.address
      )
      return res.send({
        item,
      })
    }
  )

  router.get("/kitty-items/supply", async (req: Request, res: Response) => {
    const supply = await kittyItemsService.getSupply()
    return res.send({
      supply,
    })
  })

  router.get("/onlybadges/list-merchants", async (req: Request, res: Response) => {
    const latestListings = await kittyItemsService.findMostRecentMinter(
      req.query
    )
    return res.send(latestListings)
  })

  router.get("/onlybadges/list-badges", async (req: Request, res: Response) => {
    const latestListings = await kittyItemsService.getOnlyBadges(
      req.query
    )
    return res.send(latestListings)
  })

  return router
}

export default initKittyItemsRouter
