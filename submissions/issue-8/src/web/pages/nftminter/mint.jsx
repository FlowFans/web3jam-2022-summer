import ClaimBadges from "src/components/ClaimBadges"
import NFTMinter from "src/components/NFTMinter"
import PageTitle from "src/components/PageTitle"
import useAppContext from "src/hooks/useAppContext"

export default function Mint() {

  return (
    <div>
      <PageTitle>Setup Profile</PageTitle>
      <main>
        <div className="main-container py-14">
          <NFTMinter />
        </div>
      </main>
    </div>
  )
}
