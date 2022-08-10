import ClaimBadges from "src/components/ClaimBadges"
import BadgesMinter from "src/components/BadgesMinter"
import PageTitle from "src/components/PageTitle"
import useAppContext from "src/hooks/useAppContext"
import Footer from "src/components/Footer"

export default function Mint() {
//   const {isLoggedInAsAdmin, setShowAdminLoginDialog} = useAppContext()

//   const onAdminLoginClick = () => {
//     setShowAdminLoginDialog(true)
//   }

//   if (!isLoggedInAsAdmin) {
//     return (
//       <div className="flex items-center justify-center mt-14">
//         <button onClick={onAdminLoginClick}>Log In to Admin View</button>
//       </div>
//     )
//   }

  return (
    <div>
      <PageTitle>Create a new badge</PageTitle>
      <main>
        <div className="main-container py-14">
          <BadgesMinter />
        </div>
      </main>
      <Footer/>
    </div>
  )
}
