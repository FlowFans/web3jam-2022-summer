import Home from '../pages/Home'
import AboutUs from '../pages/AboutUs'
import Donate from '../pages/Donate'
import Market from '../pages/Market'
import NFTDetails from '../pages/NFTDetails'
import Organization from '../pages/Organization';
import WannaDonate from '../pages/WannaDonate'
import App from '../App'
import {BrowserRouter as Router, Routes, Route} from 'react-router-dom'

const BaseRouter = () => (
    <Router>
        <Routes>
            <Route path='/' element={<App />}>
            <Route path='/home' element={<Home />}></Route>
            <Route path='/aboutus' element={<AboutUs />}></Route>
            <Route path='/donate' element={<Donate />}></Route>
            <Route path='/market' element={<Market />}></Route>
            <Route path='/nftdetails/:id' element={<NFTDetails />}></Route>
            <Route path='/organization' element={<Organization />}></Route>
            <Route path='/wannadonate' element={<WannaDonate />}></Route>
            </Route>
        </Routes>
    </Router>
)


export default BaseRouter