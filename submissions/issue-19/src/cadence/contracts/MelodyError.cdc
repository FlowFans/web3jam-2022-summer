pub contract MelodyError {

  pub enum ErrorCode: UInt8 {
    pub case NO_ERROR
    pub case PAUSED
    pub case NOT_EXIST
    pub case INVALID_PARAMETERS
    pub case NEGATIVE_VALUE_NOT_ALLOWED
    pub case ALREADY_EXIST
    pub case CAN_NOT_BE_ZERO
    pub case SAME_BOOL_STATE
    pub case WRONG_LIFE_CYCLE_STATE
    pub case ACCESS_DENIED
    pub case PAYMENT_NOT_REVOKABLE
    pub case NOT_TRANSFERABLE
    pub case TYPE_MISMATCH

  }

  pub fun errorEncode(msg: String, err: ErrorCode): String {
    return "[MelodyErrorMsg:".concat(msg).concat("]").concat("[MelodyErrorCode:").concat(err.rawValue.toString()).concat("]")
  }

  init() {
  }
}