// SPDX-License-Identifier: GPL-3.0

pragma solidity 0.8.9;

interface IWakandaPass {
    /**
     * @notice This will burn your original land and mint 32 sub-lands, all of which are yours
     * @param tokenId tokenId of land which you want to divide
     */
    function divide(uint256 tokenId) external;

    /**
     * @notice This will burn your original land and mint 32 sub-lands, all of which are yours
     * @param geohash geohash of land which you want to divide
     */
    function divideByGeohash(string memory geohash) external;

    /**
     * @notice Query tokenId by geohash
     * @param geohash geohash you want to query
     * @return tokenId the query token's id which is not necessarily 100% valid
     * @return exist if the query token is exist, return true
     */
    function tokenByGeohash(string memory geohash)
        external
        view
        returns (uint256 tokenId, bool exist);

    /**
     * @notice Renounce the ownership of the token
     * @param tokenId tokenId you want to renounce
     */
    function renounce(uint256 tokenId) external;

    /**
     * @notice Renounce the ownership of the token
     * @param geohash geohash you want to renounce
     */
    function renounceByGeohash(string memory geohash) external;

    /**
     * @notice Claim a token from No Man's Land
     * @param tokenId tokenId you want to claim
     */
    function claim(uint256 tokenId) external;

    /**
     * @notice Claim a token from No Man's Land
     * @param geohash geohash you want to claim
     */
    function claimByGeohash(string memory geohash) external;
}
