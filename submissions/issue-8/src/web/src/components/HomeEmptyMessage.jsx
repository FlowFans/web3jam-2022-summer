import {paths} from "src/global/constants"
import useAppContext from "src/hooks/useAppContext"
import useLogin from "src/hooks/useLogin"
import Button, {ButtonLink} from "./Button"
import { useFileUpload } from 'src/hooks/useUpload'
import {useRouter} from "next/router"




export default function HomeEmptyMessage() {
  const logIn = useLogin()
  const {switchToAdminView} = useAppContext()
  const {currentUser} = useAppContext()
  const [file, selectFile] = useFileUpload()
  const router = useRouter()

  const logInOrMint = () => {
    if(!currentUser) {
      logIn();
    }
    else {
      router.push(paths.claimBadges)
    }
  }

  return (
      

    <div className="justify-items-center mt-10">
      <div className="flex bg-white text-center pb-1 md:pb-1 ">
        <h1 className="flex-auto text-6xl md:text-8xl font-extrabold leading-normal tracking-tighter mt-14 mb-10">Make your idea to be 
        <p className="break-words" >
        <span className="bg-clip-text text-transparent bg-gradient-to-tl from-blue-500  via-orange-500  via-indigo-600 via-red-300 to-teal-400">OnlyBadge</span>
        </p>
        </h1>
      </div>

      <div className="bg-white text-center mt-10 ">
        <div className="bg-white rounded-md inline-flex flex-col justify-center stroke-2">
            {/* <img
              src="/images/newonlybadgelogo.svg"
              alt="Only Badge"
              width="280"
              className="mx-auto"
            /> */}
            <p className="text-gray-light mb-5 mt-1">
              Get started by minting your first Badges!
            </p>
            
            <Button onClick={logInOrMint} className="bg-gradient-to-tl from-blue-500 via-red-300 to-violet-500 ring-2 ring-indigo-300 ring-opacity-50">
              Claim Now
            </Button>
            
            

            {/* <hr className="mt-8 mb-6" /> */}

            {/* <b>Learn more about Only Badge</b>
            <p className="text-gray-light mb-5 mt-1 max-w-xs mx-auto">
              Learn more about the key components and services that make Badge possible.
            </p> */}

            {/* <ButtonLink href={paths.githubRepo} target="_blank" color="outline">
              VIEW DOCUMENTATION & RESOURCES
            </ButtonLink>  */}
          </div>


      </div>
        
      </div>
  )
}
