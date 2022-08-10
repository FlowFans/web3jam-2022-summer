/// Pausable
///
/// The interface that pausable contracts implement.
///
pub contract interface Pausable {
    /// paused
    /// If current contract is paused
    ///
    access(contract) var paused: Bool

    /// Paused
    ///
    /// Emitted when the pause is triggered.
    pub event Paused()

    /// Unpaused
    ///
    /// Emitted when the pause is lifted.
    pub event Unpaused()

    /// Pausable Checker
    /// 
    /// some methods to check if paused
    /// 
    pub resource interface Checker {
        /// Returns true if the contract is paused, and false otherwise.
        ///
        pub fun paused(): Bool;

        /// a function callable only when the contract is not paused.
        /// 
        /// Requirements:
        /// - The contract must not be paused.
        ///
        access(contract) fun whenNotPaused() {
            pre {
                !self.paused(): "Pausable: paused"
            }
        }

        /// a function callable only when the contract is paused.
        /// 
        /// Requirements:
        /// - The contract must be paused.
        ///
        access(contract) fun whenPaused() {
            pre {
                self.paused(): "Pausable: not paused"
            }
        }
    }

    /// Puasable Pauser
    ///
    pub resource interface Pauser {
        /// pause
        /// 
        pub fun pause();

        /// unpause
        ///
        pub fun unpause();
    }
}
