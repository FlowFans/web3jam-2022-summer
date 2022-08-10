pub contract Existence {
    pub let existencePublicPath: PublicPath;
    
    pub resource interface ExistProof {
        
    }

    init() {
        self.existencePublicPath = PublicPath(identifier: "existencePublicPath".concat(self.account.address.toString()))!
    }

    pub fun getExistProofFromAddress(addr: Address): &{Existence.ExistProof}? {
        let pubAcct = getAccount(addr);
        let ep = pubAcct.getCapability<&{Existence.ExistProof}>(self.existencePublicPath);
        return ep.borrow();
    }
}