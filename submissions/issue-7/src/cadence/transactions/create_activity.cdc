import NebulaActivity from 0x01
import ExampleToken from 0x05

transaction(
activityName: String,
description: String,
hostName: String,
tags: [String],
metaDataVerifiedURL: String,
metaDataNotVerifiedURL: String,
) {
    let activityInfo: NebulaActivity.ActivityInfo

    prepare(signer: AuthAccount) {
        let manager = signer.borrow<&NebulaActivity.ActivitiesManager>(from: /storage/ActivitiesManager)
            ?? panic("Your account don't have an activities manager")
        // Initialize the start time
        let startTime = NebulaActivity.Date(
        _year: 2022,
        _month: 8,
        _day: 1,
        _hour: 9,
        _minute: 0
        )
        // Initialize the end time
        let endTime = NebulaActivity.Date(
        _year: 2022,
        _month: 8,
        _day: 2,
        _hour: 9,
        _minute: 0
        )
        // Initialize the activityInfo
        self.activityInfo = NebulaActivity.ActivityInfo(
        _startTime: startTime,
        _endTime: endTime,
        _activityName: activityName,
        _descriptiion: description,
        _hostName: hostName,
        _tags: tags
        )

        // Create this new activity in this manager
        let ticketMachine <- NebulaActivity.createTicketMachine(
        activityInfo: self.activityInfo,
        metaDataVerified: metaDataVerifiedURL,
        metaDataNotVerified: metaDataNotVerifiedURL,
        )

        manager.depositActivity(ticketMachine: <- ticketMachine)
        log("Successfully made a new acti")
    }

    execute {
    }
}
