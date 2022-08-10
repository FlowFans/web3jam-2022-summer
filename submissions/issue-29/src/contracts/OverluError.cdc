pub contract OverluError {
  pub enum ErrorCode: UInt8 {
    pub case NO_ERROR
    
    pub case INVALID_PARAMETERS
    pub case NEGATIVE_VALUE_NOT_ALLOWED
    pub case WHITE_LIST_EXIST
    pub case ALREADY_EXIST
    pub case LOST_PUBLIC_CAPABILITY // 4
    pub case EXCEEDED_AMOUNT_LIMIT
    pub case INVALID_USER_CERTIFICATE
    pub case MISMATCH_RESOURCE_TYPE 
    pub case ACCESS_DENY
    pub case INVALID_BALANCE_AMOUNT
    pub case SAME_BOOL_STATE
    pub case RESOURCE_ALREADY_EXIST
    pub case CONTRACT_PAUSE
    pub case EDITION_NUMBER_EXCEED
    pub case NOT_OPEN
    pub case NOT_ENOUGH_BALANCE
    pub case INSUFFICIENT_ENERGY
    pub case NOT_EXIST
    pub case CAN_NOT_BE_ZERO
  }

  pub fun errorEncode(msg: String, err: ErrorCode): String {
    return "[OverluErrorMsg:".concat(msg).concat("]").concat("[OverluErrorCode:").concat(err.rawValue.toString()).concat("]")
  }
  
  init() {
  }
}