import Mist from "../contracts/Mist.cdc"

pub struct ClaimStatus {
    pub let availability: Mist.Availability
    pub let eligibilityForRegistration: Mist.Eligibility
    pub let eligibilityForClaim: Mist.Eligibility

    init(
        availability: Mist.Availability,
        eligibilityForRegistration: Mist.Eligibility,
        eligibilityForClaim: Mist.Eligibility
    ) {
        self.availability = availability
        self.eligibilityForRegistration = eligibilityForRegistration
        self.eligibilityForClaim = eligibilityForClaim
    }
}

pub fun main(raffleID: UInt64, host: Address, claimer: Address): ClaimStatus {
    let raffleCollection =
        getAccount(host)
        .getCapability(Mist.RaffleCollectionPublicPath)
        .borrow<&Mist.RaffleCollection{Mist.IRaffleCollectionPublic}>()
        ?? panic("Could not borrow IRaffleCollectionPublic from address")

    let raffle = raffleCollection.borrowPublicRaffleRef(raffleID: raffleID)
        ?? panic("Could not borrow raffle")

    let availability = raffle.checkAvailability(params: {})
    let eligibilityR = raffle.checkRegistrationEligibility(account: claimer, params: {})
    let eligibilityC = raffle.checkClaimEligibility(account: claimer, params: {})

    return ClaimStatus(
        availability: availability,
        eligibilityForRegistration: eligibilityR,
        eligibilityForClaim: eligibilityC
    )
}
 