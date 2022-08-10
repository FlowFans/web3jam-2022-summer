// Import the NFTStorage class and File constructor from the 'nft.storage' package
import { NFTStorage, File } from 'nft.storage'

import { useState } from "react"

// The 'mime' npm package helps us set the correct file type on our File objects
import mime from 'mime'

// The 'fs' builtin module on Node.js provides access to the file system
// import fs from 'fs'

// The 'path' module provides helpers for manipulating filesystem paths
// import path from 'path'

// Paste your NFT.Storage API key into the quotes:
const NFT_STORAGE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJkaWQ6ZXRocjoweEUzRDI3YmQ5YzMyMTA2NDFmN2EwNDIzM2UwNkFjQTg4NzE3MjczNTgiLCJpc3MiOiJuZnQtc3RvcmFnZSIsImlhdCI6MTY1ODM3MTc1OTYxNSwibmFtZSI6Im9ubHliYWRnZXMifQ.io3-0ZvkNA4RmvPlw3Lfbow8aXD-vd5F_FeAsYOi5yE'

/**
  * Reads an image file from `imagePath` and stores an NFT with the given name and description.
  * @param {File} image the path to an image file
  * @param {string} name a name for the NFT
  * @param {string} description a text description for the NFT
  */

/**
  * A helper to read a file from a location on disk and return a File object.
  * Note that this reads the entire file into memory and should not be used for
  * very large files. 
  * @param {string} filePath the path to a file to store
  * @returns {File} a File object containing the file content
  */
// async function fileFromPath(filePath) {
//     const content = await fs.promises.readFile(filePath)
//     const type = mime.getType(filePath)
//     return new File([content], path.basename(filePath), { type })
// }

export default function useNFTStorage() {
    // const [imagePath, name, description] = args
    const [isUploading, setUploading] = useState(false)
    // const result = await storeNFT(imagePath, name, description)
    // console.log(result)

    function storeNFT(image, name, description, onSuccess, onFailure) {
        // create a new NFTStorage client using our API key
        const nftstorage = new NFTStorage({ token: NFT_STORAGE_KEY })
        setUploading(true)
        // call client.store, passing in the image & metadata
        try {
            nftstorage.store({
            image,
            name,
            description,
                }).then(response => {
                    setUploading(false)
                    console.log(response.url)
                    console.log(response.data)
                    console.log(response.ipnft)
                    onSuccess(response.url, response.data, response.ipnft)
                })
        }
        catch(error) {
            setUploading(false)
            onFailure(error)
        }
    }
    

    return [isUploading, storeNFT]
}

/**
 * The main entry point for the script that checks the command line arguments and
 * calls storeNFT.
 * 
 * To simplify the example, we don't do any fancy command line parsing. Just three
 * positional arguments for imagePath, name, and description
 */
// async function main() {
//     const args = process.argv.slice(2)
//     if (args.length !== 3) {
//         console.error(`usage: ${process.argv[0]} ${process.argv[1]} <image-path> <name> <description>`)
//         process.exit(1)
//     }

//     const [imagePath, name, description] = args
//     const result = await storeNFT(imagePath, name, description)
//     console.log(result)
// }