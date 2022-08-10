import AdminNav from "src/components/AdminNav"
import PageTitle from "src/components/PageTitle"
import useAppContext from "src/hooks/useAppContext"

export default function Mint() {
  const {isLoggedInAsAdmin, setShowAdminLoginDialog} = useAppContext()

  const onAdminLoginClick = () => {
    setShowAdminLoginDialog(true)
  }

  if (!isLoggedInAsAdmin) {
    return (
      <div className="flex items-center justify-center mt-14">
        <button onClick={onAdminLoginClick}>Log In to Admin View</button>
      </div>
    )
  }

  return (
    <div>
      <PageTitle>Mint</PageTitle>
      <main>
        <div className="main-container py-20">
          <AdminNav />
        </div>
      </main>
    </div>
  )
}
