import HomeEmptyMessage from "src/components/HomeEmptyMessage"
// import LatestMarketplaceItems from "src/components/LatestMarketplaceItems"
import LatestStoreItems from "src/components/LatestStoreItems"
import PopularMerchants from "src/components/PopularMerchants"
import PageTitle from "src/components/PageTitle"
import Footer from "src/components/Footer"
import useApiListMerchants from "src/hooks/useApiListMerchants"
import useApiListBadges from "src/hooks/useApiListBadges"

export default function Home() {
  const {listings, isLoading} = useApiListMerchants()
  const { badges, badgesLoading} = useApiListBadges()

  return (
    <div>
      <PageTitle>Home</PageTitle>
      <main>
        {
      (listings && listings.length > 0 ? (
        <>
          {/* <PopularMerchants items={listings} /> */}
          <LatestStoreItems items={badges} />
        </>
      )
          : (
        <HomeEmptyMessage />
      ))
          }
      </main>
      <Footer/>
    </div>
  )
}