import StarRealm from "../../contracts/StarRealm.cdc"

pub fun main(to: Address) {
    let pubAcct = getAccount(to);
    let docker = pubAcct.getCapability<&{StarRealm.StarDocker}>(StarRealm.DockerPublicPath).borrow();

    if docker == nil {
        panic("Target docker does not exists!")
    }
    
    // if let targetDocker = StarRealm.getStarDockerFromAddress(addr: to) {
        
    // } else {
    //     panic("Target docker does not exists!")
    // }
}