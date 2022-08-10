pub fun main() {
  let a = 2
  let mixValue : {Address: {String:Bool}} = {}
  log("Orignal Value")
  log(mixValue)

  var old = mixValue.insert(key:0x0fadfa,{"Kiko":true})
  log("First Change Value")
  log(mixValue)

  log("First Old Value")
  log(old)

  old = mixValue.insert(key:0x0fadfa,{"Kiko":false})
  log("Second Change Value")
  log(mixValue)

  log("Second Old Value")
  log(old)

}
