pub contract Profile {
  pub let publicPath: PublicPath
  pub let privatePath: StoragePath

  pub resource interface Public {
    pub fun getName(): String
    pub fun getClassification(): String
    pub fun asReadOnly(): Profile.ReadOnly
  }
  
  pub resource interface Owner {
    pub fun getName(): String
    pub fun getClassification(): String
    
    pub fun setName(_ name: String) {
      pre {
        name.length <= 15: "Names must be under 15 characters long."
      }
    }
    pub fun setClassification(_ classification: String) {
      pre {
        classification == "Learner" || classification == "Teacher": "Class only supports \'Learner\' or \'Teacher\'."
      }
    }
  }
  
  pub resource Base: Owner, Public {
    access(self) var name: String
    access(self) var classification: String
    
    init() {
      self.name = "Zhixiny"
      self.classification = "Learner"
    }
    
    pub fun getName(): String { return self.name }
    pub fun getClassification(): String { return self.classification }
    
    pub fun setName(_ name: String) { self.name = name }
    pub fun setClassification(_ classification: String) { self.classification = classification }
    
    pub fun asReadOnly(): Profile.ReadOnly {
      return Profile.ReadOnly(
        address: self.owner?.address,
        name: self.getName(),
        classification: self.getClassification(),
      )
    }
  }

  pub struct ReadOnly {
    pub let address: Address?
    pub let name: String
    pub let classification: String
    
    init(address: Address?, name: String, classification: String) {
      self.address = address
      self.name = name
      self.classification = classification
    }
  }
  
  pub fun new(): @Profile.Base {
    return <- create Base()
  }
  
  pub fun check(_ address: Address): Bool {
    return getAccount(address)
      .getCapability<&{Profile.Public}>(Profile.publicPath)
      .check()
  }
  
  pub fun fetch(_ address: Address): &{Profile.Public} {
    return getAccount(address)
      .getCapability<&{Profile.Public}>(Profile.publicPath)
      .borrow()!
  }
  
  pub fun read(_ address: Address): Profile.ReadOnly? {
    if let profile = getAccount(address).getCapability<&{Profile.Public}>(Profile.publicPath).borrow() {
      return profile.asReadOnly()
    } else {
      return nil
    }
  }
    
  init() {
    self.publicPath = /public/profile_1
    self.privatePath = /storage/profile_1
    
    if (!Profile.check(self.account.address)) {
      self.account.save(<- self.new(), to: self.privatePath)
      self.account.link<&Base{Public}>(self.publicPath, target: self.privatePath)
    }
    
    self.account
      .borrow<&Base{Owner}>(from: self.privatePath)!
      .setName("Zhixiny")
    self.account
      .borrow<&Base{Owner}>(from: self.privatePath)!
      .setClassification("Learner")
  }
}