import NebulaActivity from "../contract/NebulaActivity.cdc"
import ExampleToken from "../contract/ExampleToken.cdc"

transaction(
activityName: String,
description: String,
hostName: String,
tags: [String],
metaDataVerifiedURL: String,
metaDataNotVerifiedURL: String,
startYear: UInt64,
startMonth: UInt64,
startDay: UInt64,
startHour: UInt64,
startMinute: UInt64,
endYear: UInt64,
endMonth: UInt64,
endDay: UInt64,
endtHour: UInt64,
endMinute: UInt64
) {
    let activityInfo: NebulaActivity.ActivityInfo

    prepare(signer: AuthAccount) {
        let manager = signer.borrow<&NebulaActivity.ActivitiesManager>(from: /storage/ActivitiesManager)
            ?? panic("Your account don't have an activities manager")
        // Initialize the start time
        let startTime = NebulaActivity.Date(
        _year: startYear,
        _month: startMonth,
        _day: startDay,
        _hour: startHour,
        _minute: startMinute
        )
        // Initialize the end time
        let endTime = NebulaActivity.Date(
        _year: endYear,
        _month: endMonth,
        _day: endDay,
        _hour: endtHour,
        _minute: endMinute
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
