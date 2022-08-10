/**
 * Listing Event Handler
 *
 * This worker extends Base Event Handler & listens for Listing events from Flow.
 * It will run callback functions to INSERT or DELETE entries from the listing table.
 *
 */
import * as fcl from "@onflow/fcl";
import { KittyItemsService } from "services/kitty-items";
import { BlockCursorService } from "../services/block-cursor";
import { FlowService } from "../services/flow";
import { StorefrontService } from "../services/storefront";
import { BaseEventHandler } from "./base-event-handler";

class ListingHandler extends BaseEventHandler {
  private eventListingAvailable;
  private eventListingCompleted;
  private eventMinterAdded;
  private eventOnlyBadgesMinted;

  constructor(
    private readonly storefrontService: StorefrontService,
    private readonly onlybadgesService: KittyItemsService,
    blockCursorService: BlockCursorService,
    flowService: FlowService
  ) {
    super(blockCursorService, flowService, []);

    this.eventListingAvailable = `A.${fcl.sansPrefix(
      storefrontService.storefrontAddress
    )}.NFTStorefront.ListingAvailable`;

    this.eventListingCompleted = `A.${fcl.sansPrefix(
      storefrontService.storefrontAddress
    )}.NFTStorefront.ListingCompleted`;

    this.eventMinterAdded = `A.${fcl.sansPrefix(
      onlybadgesService.kittyItemsAddress
    )}.OnlyBadges.MinterAdded`;

    this.eventOnlyBadgesMinted = `A.${fcl.sansPrefix(
      onlybadgesService.kittyItemsAddress
    )}.OnlyBadges.Minted`;

    console.log("eventMinterAdded:" + this.eventMinterAdded);

    this.eventNames = [
      this.eventListingAvailable,
      this.eventListingCompleted,
      this.eventMinterAdded,
      this.eventOnlyBadgesMinted
    ];
  }

  async onEvent(event: any): Promise<void> {
    switch (event.type) {
      case this.eventListingAvailable:
        console.log("onEvent:" + JSON.stringify(event))
        await this.storefrontService.addListing(event);
        break;
      case this.eventListingCompleted:
        await this.storefrontService.removeListing(event);
        break;
      case this.eventMinterAdded:
        console.log("event:" + JSON.stringify(event))
        await this.onlybadgesService.addMinter(event);
        break;
      case this.eventOnlyBadgesMinted:
        console.log("event:" + JSON.stringify(event))
        await this.onlybadgesService.onlybadgesMinted(event);
        break;
      default:
        return;
    }
  }
}

export { ListingHandler };
