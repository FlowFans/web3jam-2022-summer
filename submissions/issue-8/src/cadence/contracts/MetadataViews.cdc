/**

This contract implements the metadata standard proposed
in FLIP-0636

Ref: https://github.com/onflow/flow/blob/master/flips/20210916-nft-metadata.md

Structs and resources can implement one or more
metadata types, called views. Each view type represents
a different kind of metadata, such as a creator biography
or a JPEG image file.
*/
import FungibleToken from "./FungibleToken.cdc"

pub contract MetadataViews {

    // A Resolver provides access to a set of metadata views.
    //
    // A struct or resource (e.g. an NFT) can implement this interface
    // to provide access to the views that it supports.
    //
    pub resource interface Resolver {
        pub fun getViews(): [Type]
        pub fun resolveView(_ view: Type): AnyStruct?
    }

    // A ResolverCollection is a group of view resolvers index by ID.
    //
    pub resource interface ResolverCollection {
        pub fun borrowViewResolver(id: UInt64): &{Resolver}
        pub fun getIDs(): [UInt64]
    }

    // Display is a basic view that includes the name and description
    // of an object. Most objects should implement this view.
    //
    pub struct Display {
        pub let name: String
        pub let description: String
        pub let thumbnail: AnyStruct{File}

        init(
            name: String,
            description: String,
            thumbnail: AnyStruct{File}
        ) {
            self.name = name
            self.description = description
            self.thumbnail = thumbnail
        }
    }

    // File is a generic interface that represents a file stored on or off chain.
    //
    // Files can be used to references images, videos and other media.
    //
    pub struct interface File {
        pub fun uri(): String
    }

    // HTTPFile is a file that is accessible at an HTTP (or HTTPS) URL. 
    //
    pub struct HTTPFile: File {
        pub let url: String

        init(url: String) {
            self.url = url
        }

        pub fun uri(): String {
            return self.url
        }
    }

    // IPFSThumbnail returns a thumbnail image for an object
    // stored as an image file in IPFS.
    //
    // IPFS images are referenced by their content identifier (CID)
    // rather than a direct URI. A client application can use this CID
    // to find and load the image via an IPFS gateway.
    //
    pub struct IPFSFile: File {

        // CID is the content identifier for this IPFS file.
        //
        // Ref: https://docs.ipfs.io/concepts/content-addressing/
        //
        pub let cid: String

        // Path is an optional path to the file resource in an IPFS directory.
        //
        // This field is only needed if the file is inside a directory.
        //
        // Ref: https://docs.ipfs.io/concepts/file-systems/
        //
        pub let path: String?

        init(cid: String, path: String?) {
            self.cid = cid
            self.path = path
        }

        // This function returns the IPFS native URL for this file.
        //
        // Ref: https://docs.ipfs.io/how-to/address-ipfs-on-web/#native-urls
        //
        pub fun uri(): String {
            if let path = self.path {
                return "ipfs://".concat(self.cid).concat("/").concat(path)
            }
            
            return "ipfs://".concat(self.cid)
        }
    }

    /// Editions is an optional view for collections that issues multiple objects
    /// with the same or similar metadata, for example an X of 100 set. This information is 
    /// useful for wallets and marketplaes.
    ///
    /// An NFT might be part of multiple editions, which is why the edition information
    /// is returned as an arbitrary sized array
    /// 
    pub struct Editions {

        /// An arbitrary-sized list for any number of editions
        /// that the NFT might be a part of
        pub let infoList: [Edition]

        init(_ infoList: [Edition]) {
            self.infoList = infoList
        }
    }

    /// A helper to get Editions in a typesafe way
    pub fun getEditions(_ viewResolver: &{Resolver}) : Editions? {
        if let view = viewResolver.resolveView(Type<Editions>()) {
            if let v = view as? Editions {
                return v
            }
        }
        return nil
    }

        /// Edition information for a single edition
    pub struct Edition {

        /// The name of the edition
        /// For example, this could be Set, Play, Series,
        /// or any other way a project could classify its editions
        pub let name: String?

        /// The edition number of the object.
        ///
        /// For an "24 of 100 (#24/100)" item, the number is 24. 
        ///
        pub let number: UInt64

        /// The max edition number of this type of objects.
        /// 
        /// This field should only be provided for limited-editioned objects.
        /// For an "24 of 100 (#24/100)" item, max is 100.
        /// For an item with unlimited edition, max should be set to nil.
        /// 
        pub let max: UInt64?

        init(name: String?, number: UInt64, max: UInt64?) {
            if max != nil {
                assert(number <= max!, message: "The number cannot be greater than the max number!")
            }
            self.name = name
            self.number = number
            self.max = max
        }
    }

    /// A view to expose a URL to this item on an external site.
    ///
    /// This can be used by applications like .find and Blocto to direct users to the original link for an NFT.
    pub struct ExternalURL {
        pub let url: String

        init(_ url: String) {
            self.url=url
        }
    }

    /// Struct to store details of a single royalty cut for a given NFT
    pub struct Royalty {

        /// Generic FungibleToken Receiver for the beneficiary of the royalty
        /// Can get the concrete type of the receiver with receiver.getType()
        /// Recommendation - Users should create a new link for a FlowToken receiver for this using `getRoyaltyReceiverPublicPath()`,
        /// and not use the default FlowToken receiver.
        /// This will allow users to update the capability in the future to use a more generic capability
        pub let receiver: Capability<&AnyResource{FungibleToken.Receiver}>

        /// Multiplier used to calculate the amount of sale value transferred to royalty receiver.
        /// Note - It should be between 0.0 and 1.0 
        /// Ex - If the sale value is x and multiplier is 0.56 then the royalty value would be 0.56 * x.
        ///
        /// Generally percentage get represented in terms of basis points
        /// in solidity based smart contracts while cadence offers `UFix64` that already supports
        /// the basis points use case because its operations
        /// are entirely deterministic integer operations and support up to 8 points of precision.
        pub let cut: UFix64

        /// Optional description: This can be the cause of paying the royalty,
        /// the relationship between the `wallet` and the NFT, or anything else that the owner might want to specify
        pub let description: String

        init(recepient: Capability<&AnyResource{FungibleToken.Receiver}>, cut: UFix64, description: String) {
            pre {
                cut >= 0.0 && cut <= 1.0 : "Cut value should be in valid range i.e [0,1]"
            }
            self.receiver = recepient
            self.cut = cut
            self.description = description
        }
    }

}
