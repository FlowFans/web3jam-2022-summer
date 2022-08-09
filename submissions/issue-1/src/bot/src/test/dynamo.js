const { getUser, queryUser } = require("../dynamodb/wakandaplus")

// getUser('3257129116894208').then((res) => console.log(res))

queryUser('833684848849453098').then((res) => console.log(res))
