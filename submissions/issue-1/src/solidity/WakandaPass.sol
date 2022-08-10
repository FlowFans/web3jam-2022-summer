// SPDX-License-Identifier: GPL-3.0

pragma solidity ^0.8.9;

import "@openzeppelin/contracts/token/ERC721/ERC721.sol";
import "@openzeppelin/contracts/token/ERC721/IERC721Receiver.sol";
import "@openzeppelin/contracts/token/ERC721/extensions/ERC721Enumerable.sol";
import "@openzeppelin/contracts/utils/Counters.sol";
import "@openzeppelin/contracts/utils/Strings.sol";
import "base64-sol/base64.sol";

import "./interfaces/IWakandaPass.sol";

/**
 * @title WakandaPass
 * @dev WakandaPass use geohash algorithm. Each NFT can be cut into smaller pieces
 * @author Wakanda Labs
 */
contract WakandaPass is
ERC721,
ERC721Enumerable,
IWakandaPass,
IERC721Receiver
{
    using Counters for Counters.Counter;

    using Strings for uint256;

    Counters.Counter private _tokenIdCounter;

    // Optional mapping for token Geohash
    mapping(uint256 => string) private _tokenGeohashes;

    // The alphabet(32ghs) uses all digits 0-9 and almost all lower case letters except "a", "i", "l" and "o"
    // https://en.wikipedia.org/wiki/WakandaPass
    bytes32 private constant ALPHABET = "0123456789bcdefghjkmnpqrstuvwxyz";

    constructor(string memory name_, string memory symbol_)
    ERC721(name_, symbol_)
    {
        _batchMint("", address(this));
    }

    /**
     * @notice This will burn your original land and mint 32 sub-lands, all of which are yours
     * @param tokenId tokenId of land which you want to divide
     */
    function divide(uint256 tokenId) external {
        require(_exists(tokenId), "WakandaPass: divide of nonexistent token");
        _divide(tokenId);
    }

    /**
     * @notice This will burn your original land and mint 32 sub-lands, all of which are yours
     * @param geohash geohash of land which you want to divide
     */
    function divideByGeohash(string memory geohash) external {
        uint256 tokenId = uint256(keccak256(abi.encodePacked(geohash)));
        _divide(tokenId);
    }

    /**
     * @notice Query tokenId by geohash
     * @dev abi.encodePacked will have many-to-one parameters and encodings, but every WakandaPass is unique
     * @param geohash geohash you want to query
     * @return tokenId the query token's id which is not necessarily 100% valid
     * @return exist if the query token is exist, return true
     */
    function tokenByGeohash(string memory geohash)
    external
    view
    returns (uint256 tokenId, bool exist)
    {
        (tokenId, exist) = _tokenByGeohash(geohash);
    }

    /**
     * @notice Query tokenId by tokenURI
     * @dev abi.encodePacked will have many-to-one parameters and encodings, but every WakandaPass is unique
     * @param geohash geohash you want to query
     * @return tokenId the query token's id which is not necessarily 100% valid
     * @return exist if the query token is exist, return true
     */
    function _tokenByGeohash(string memory geohash)
    internal
    view
    returns (uint256 tokenId, bool exist)
    {
        tokenId = uint256(keccak256(abi.encodePacked(geohash)));
        exist = _exists(tokenId);
    }

    function _divide(uint256 tokenId) internal {
        require(
            _isApprovedOrOwner(_msgSender(), tokenId),
            "WakandaPass: transfer caller is not owner nor approved"
        );
        string memory parentURI_ = _tokenGeohashes[tokenId];
        _burn(tokenId);
        _batchMint(parentURI_, _msgSender());
    }

    /**
     * @notice Batch mint by origin
     * @dev abi.encodePacked will have many-to-one parameters and encodings, but every WakandaPass is unique
     * @param origin all URI was build by alphabet
     * @param to address of the owner of the sub-lands
     */
    function _batchMint(string memory origin, address to) internal {
        for (uint8 i = 0; i < 32; i++) {
            uint256 newId = uint256(
                keccak256(abi.encodePacked(origin, ALPHABET[i]))
            );
            _tokenIdCounter.increment();
            _safeMint(to, newId);
            _setTokenGeohash(newId, string(abi.encodePacked(origin, ALPHABET[i])));
        }
    }

    // TODO add a function to query tokenURI by tokenId
    function tokenURI(uint256 tokenId)
    public
    override
    view returns (string memory) {
        require(_exists(tokenId), "WakandaPass: tokenId does not exist");
        string memory tokenURI_ = _tokenGeohashes[tokenId];
        string memory from = Strings.toHexString(tokenId >> 232);
        string memory to = Strings.toHexString(tokenId & 0xffffff);
        string memory output = string(
            abi.encodePacked(
                'data:application/json;base64,',
                Base64.encode(
                    bytes(
                        abi.encodePacked(
                            '{"name": "',
                            name(),
                            ' #',
                            tokenURI_,
                            '", "description": "Welcome to Wakanda Metaverse!", "image": "data:image/svg+xml;base64,',
                            Base64.encode(
                                bytes(
                                    string(
                                        abi.encodePacked(
                                            '<svg width="800" height="800" xmlns="http://www.w3.org/2000/svg" fill="none"><rect width="800" height="800" fill="url(#paint_liner)"/><defs><linearGradient id="paint_liner" x1="0" y1="400" x2="800" y2="400" gradientUnits="userSpaceOnUse"><stop stop-color="#',
                                            string(abi.encodePacked(bytes(from)[2], bytes(from)[3], bytes(from)[4], bytes(from)[5], bytes(from)[6], bytes(from)[7])),
                                            '"/><stop offset="1" stop-color="#',
                                            string(abi.encodePacked(bytes(to)[2], bytes(to)[3], bytes(to)[4], bytes(to)[5], bytes(to)[6], bytes(to)[7])),
                                            '"/></linearGradient></defs><g><rect id="svg_1" fill="white" rx="24" height="400" width="635" y="200" x="83"/> <path id="svg_2" fill="#222222" d="m190.113,256.008l0,23.011l-3.858,0l0,-4.174c-0.931,1.676 -2.12,2.955 -3.567,3.835c-1.447,0.88 -3.068,1.32 -4.861,1.32c-3.335,0 -6.022,-1.136 -8.061,-3.408c-2.04,-2.272 -3.059,-5.277 -3.059,-9.015c0,-3.789 0.99,-6.803 2.97,-9.041c1.981,-2.239 4.629,-3.358 7.946,-3.358c1.947,0 3.635,0.432 5.066,1.295c1.43,0.864 2.585,2.15 3.465,3.86l0,-4.325l3.959,0zm-3.858,11.72c0,-2.599 -0.716,-4.691 -2.148,-6.275c-1.432,-1.585 -3.298,-2.377 -5.598,-2.377c-2.387,0 -4.248,0.73 -5.586,2.189c-1.337,1.458 -2.004,3.495 -2,6.11c0,2.784 0.672,4.93 2.018,6.439c1.346,1.508 3.253,2.263 5.723,2.264c2.37,0 4.228,-0.734 5.573,-2.201c1.345,-1.466 2.018,-3.516 2.019,-6.149l-0.001,0z"/><path id="svg_3" fill="#222222" d="m192.128,279.934l0,-38.98l3.986,0l0,26.406l11.196,-10.437l5.306,0l-11.602,10.538l12.643,12.473l-5.56,0l-11.983,-12.146l0,12.146l-3.986,0z"/> <path id="svg_4" fill="#222222" d="m233.197,255.975l0,23.012l-3.859,0l0,-4.175c-0.931,1.677 -2.12,2.955 -3.567,3.835c-1.447,0.88 -3.067,1.32 -4.861,1.32c-3.335,0 -6.022,-1.136 -8.061,-3.407c-2.039,-2.272 -3.059,-5.277 -3.06,-9.016c0,-3.789 0.99,-6.803 2.971,-9.041c1.98,-2.238 4.629,-3.357 7.947,-3.357c1.946,0 3.634,0.431 5.064,1.295c1.431,0.863 2.586,2.15 3.466,3.86l0,-4.326l3.96,0zm-3.859,11.72c0,-2.599 -0.715,-4.69 -2.145,-6.275c-1.431,-1.584 -3.297,-2.376 -5.598,-2.377c-2.386,0 -4.248,0.73 -5.586,2.189c-1.337,1.459 -2.006,3.496 -2.005,6.111c0,2.783 0.673,4.929 2.018,6.438c1.346,1.509 3.254,2.263 5.725,2.263c2.37,0 4.227,-0.733 5.573,-2.2c1.345,-1.468 2.018,-3.517 2.018,-6.149z"/> <path id="svg_5" fill="#222222" d="m234.995,278.978l0,-23.011l3.884,0l0,3.546c0.999,-1.475 2.201,-2.573 3.605,-3.294c1.405,-0.721 3.03,-1.082 4.875,-1.082c2.742,0 4.883,0.721 6.423,2.163c1.54,1.442 2.31,3.446 2.309,6.012l0,15.666l-4.063,0l0,-13.555c0,-2.33 -0.495,-4.061 -1.485,-5.193c-0.989,-1.132 -2.517,-1.698 -4.582,-1.698c-1.185,0 -2.264,0.214 -3.237,0.642c-0.944,0.406 -1.775,1.033 -2.423,1.825c-0.493,0.607 -0.852,1.31 -1.054,2.062c-0.212,0.771 -0.317,2.113 -0.317,4.025l0,11.892l-3.935,0z"/> <path id="svg_6" fill="#222222" d="m280.924,240l0,38.981l-3.859,0l0,-4.175c-0.914,1.659 -2.099,2.934 -3.554,3.823c-1.455,0.889 -3.08,1.333 -4.875,1.334c-3.334,0 -6.02,-1.136 -8.06,-3.408c-2.039,-2.272 -3.059,-5.277 -3.059,-9.015c0,-3.773 0.998,-6.782 2.996,-9.028c1.997,-2.247 4.655,-3.37 7.972,-3.371c1.93,0 3.605,0.432 5.026,1.295c1.421,0.864 2.572,2.15 3.451,3.859l0,-20.295l3.962,0zm-3.859,27.588c0,-2.565 -0.719,-4.631 -2.158,-6.198c-1.438,-1.567 -3.334,-2.351 -5.686,-2.353c-2.353,0 -4.19,0.713 -5.51,2.138c-1.32,1.426 -1.98,3.395 -1.98,5.91c0,2.834 0.669,5.051 2.008,6.652c1.338,1.601 3.183,2.401 5.534,2.401c2.438,0 4.346,-0.754 5.725,-2.263c1.38,-1.509 2.069,-3.604 2.067,-6.287z"/> <path id="svg_7" fill="#222222" d="m305.915,255.957l0,23.012l-3.859,0l0,-4.174c-0.931,1.676 -2.12,2.955 -3.566,3.835c-1.447,0.88 -3.068,1.32 -4.863,1.32c-3.334,0 -6.02,-1.136 -8.06,-3.407c-2.039,-2.272 -3.059,-5.277 -3.059,-9.016c0,-3.789 0.99,-6.803 2.97,-9.041c1.98,-2.238 4.629,-3.357 7.946,-3.357c1.947,0 3.635,0.431 5.066,1.295c1.43,0.863 2.585,2.15 3.465,3.86l0,-4.327l3.96,0zm-3.859,11.721c0,-2.599 -0.715,-4.69 -2.145,-6.275c-1.43,-1.584 -3.296,-2.376 -5.598,-2.377c-2.386,0 -4.248,0.73 -5.585,2.189c-1.337,1.458 -2.006,3.495 -2.008,6.111c0.001,2.782 0.674,4.928 2.021,6.439c1.346,1.511 3.254,2.266 5.725,2.264c2.369,0 4.226,-0.734 5.572,-2.201c1.346,-1.467 2.018,-3.517 2.018,-6.15z"/> <path id="svg_8" fill="#222222" d="m169.705,245.191l-8.523,22.056l-9.399,-22.056l-0.027,0.011l-0.004,-0.011l-6.559,0l5.308,12.966l-3.512,9.09l-9.4,-22.056l-0.026,0.011l-0.004,-0.011l-6.559,0l14.129,34.512l2.153,-5.567l4.74,-12.265l7.3,17.832l2.153,-5.567l11.185,-28.945l-2.955,0z"/><text xml:space="preserve" text-anchor="start" font-family="Noto Sans JP" font-size="32" id="svg_9" y="450" x="125" stroke-width="0" stroke="#000" fill="#000000">#',
                                            tokenURI_,
                                            '</text></g></svg>'
                                        )
                                    )
                                )
                            ),
                            '", "attributes": [{"trait_type": "Geohash", "value": "',
                            tokenURI_,
                            '"}, {"trait_type": "Level", "value": ',
                            Strings.toString(bytes(tokenURI_).length),
                            '}]}'
                        )
                    )
                )
            )
        );
        return output;
    }

    /**
     * @notice Renounce the ownership of the token
     * @param tokenId tokenId you want to renounce
     */
    function renounce(uint256 tokenId) external {
        safeTransferFrom(_msgSender(), address(this), tokenId);
    }

    /**
     * @notice Renounce the ownership of the token
     * @param geohash geohash you want to renounce
     */
    function renounceByGeohash(string memory geohash) external {
        (uint256 tokenId,) = _tokenByGeohash(geohash);
        safeTransferFrom(_msgSender(), address(this), tokenId);
    }

    /**
     * @notice Claim a token from No Man's Land
     * @param tokenId tokenId you want to claim
     */
    function claim(uint256 tokenId) external {
        require(_exists(tokenId), "WakandaPass: tokenId does not exist");
        _safeTransfer(address(this), _msgSender(), tokenId, "");
    }

    /**
     * @notice Claim a token from No Man's Land
     * @param geohash geohash you want to claim
     */
    function claimByGeohash(string memory geohash) external {
        (uint256 tokenId, bool exist) = _tokenByGeohash(geohash);
        require(exist, "WakandaPass: tokenURI does not exist");
        _safeTransfer(address(this), _msgSender(), tokenId, '');
    }

    // The following functions are overrides required by Solidity.

    function _beforeTokenTransfer(
        address from_,
        address to_,
        uint256 tokenId
    ) internal override(ERC721, ERC721Enumerable) {
        super._beforeTokenTransfer(from_, to_, tokenId);
    }

    function _burn(uint256 tokenId)
    internal
    override(ERC721)
    {
        super._burn(tokenId);

        if (bytes(_tokenGeohashes[tokenId]).length != 0) {
            delete _tokenGeohashes[tokenId];
        }
    }

    function supportsInterface(bytes4 interfaceId)
    public
    view
    override(ERC721, ERC721Enumerable)
    returns (bool)
    {
        return super.supportsInterface(interfaceId);
    }

    function onERC721Received(
        address operator,
        address from,
        uint256 tokenId,
        bytes calldata data
    ) external pure returns (bytes4) {
        return this.onERC721Received.selector;
    }

    /**
     * @dev Sets `geohash` as the Geohash of `tokenId`.
     *
     * Requirements:
     *
     * - `tokenId` must exist.
     */
    function _setTokenGeohash(uint256 tokenId, string memory geohash) internal virtual {
        require(_exists(tokenId), "WakandaPass: URI set of nonexistent token");
        _tokenGeohashes[tokenId] = geohash;
    }
}
