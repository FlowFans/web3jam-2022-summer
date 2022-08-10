import ClaimBadges from "src/components/ClaimBadges"
// import Minter from "src/components/BadgesMinter"
import PageTitle from "src/components/PageTitle"
import useAppContext from "src/hooks/useAppContext"
import Footer from "src/components/Footer"

export default function Claim() {
  return (
    <div>
      <PageTitle>Claim badges</PageTitle>
      <main>
        <div className="main-container mx-auto py-20">
          <ClaimBadges />
        </div>
      </main>
      <Footer/>
    </div>
  )
}
