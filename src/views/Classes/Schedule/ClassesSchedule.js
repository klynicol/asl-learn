import React, { useEffect, useState } from "react";
import AslHeader from "components/AslHeader";
import Footer from "components/mat-kit/Footer/Footer.js";
import { makeStyles } from "@material-ui/core/styles";
import classNames from "classnames";
import Parallax from "components/mat-kit/Parallax/Parallax";
import GridContainer from "components/mat-kit/Grid/GridContainer";

import styles from "assets/mat-kit/jss/material-kit-react/views/landingPage.js";
import Connection from "services/Connection";
//https://www.npmjs.com/package/react-calendar
import Calendar from "react-calendar";
import "react-calendar/dist/Calendar.css";
import Utility from "services/Utility";
import "./classes_schedule.css";
import MeetingPublicView from "./MeetingPublicView";
import { container, title } from "assets/mat-kit/jss/material-kit-react.js";

const useStyles = makeStyles((theme) => {
  return {
    ...styles,
    ...{
      container,
      description: {
        margin: "1.071rem auto 0",
        maxWidth: "600px",
        color: "#999",
        textAlign: "center !important",
        paddingTop: 20,
      },
      displayDate: {
        color: "#999",
      },
      alignCenter: {
        textAlign: "center",
      },
    },
  };
});
const con = new Connection();

export default function ClassesSchedule() {
  const classes = useStyles();

  const [value, onChange] = useState(new Date());
  const [meetings, setMeetings] = useState([]);
  const [meetingDates, setMeetingDates] = useState([]);

  useEffect(() => {
    con.guestGet("guest/meeting/getall").then((result) => {
      if (result.data.meetings) {
        console.log("public meetings", result.data.meetings);
        setMeetings(
          result.data.meetings.map((meeting) => {
            meeting.date_time = new Date(meeting.date_time + " UTC");
            meeting.id = +meeting.id;
            return meeting;
          })
        );
      }
    });
  }, []);

  useEffect(() => {
    /**
     * when meeitings change also build the new dates array so the calander
     * can quickly compare for styles.
     */
    console.log(meetings);
    setMeetingDates(
      meetings.map((meeting) => {
        return meeting.date_time.toLocaleDateString({ dateStyle: "short" });
      })
    );
  }, [meetings]);

  function calcTileClassName(activeStartDate, date, view) {
    if (
      meetingDates.includes(date.toLocaleDateString({ dateStyle: "short" }))
    ) {
      return "sched_date";
    }
    return null;
  }

  function renderActiveDate() {
    let hasMeeting = false;
    const html = meetings.map((meeting, index) => {
      if (Utility.isSameDate(meeting.date_time, value)) {
        hasMeeting = true;
        return (
          <MeetingPublicView key={index} meeting={meeting}></MeetingPublicView>
        );
      }
    });
    if (hasMeeting) {
      return html;
    }
    return (
      <div style={{ textAlign: "center" }}>
        <h3>No scheduled classes on this date.</h3>
      </div>
    );
  }

  return (
    <div>
      <AslHeader />
      <Parallax
        small
        filter
        style={{ height: 209 }}
        image={require("assets/mat-kit/img/profile-bg.jpg").default}
      />
      <div className={classNames(classes.main, classes.mainRaised)}>
        <div>
          <div className={classes.container}>
            <div className={classes.description}>
              <p>
                Take a look through the calander for upcoming classes. Click and
                register.
              </p>
            </div>
            <div className={classes.alignCenter}>
              <Calendar
                onChange={onChange}
                value={value}
                tileClassName={({ activeStartDate, date, view }) => {
                  return calcTileClassName(activeStartDate, date, view);
                }}
              />
            </div>
            <div>
              <h2 className={classes.displayDate}>
                {value && Utility.dateString(value)}
              </h2>
              <div>{renderActiveDate()}</div>
            </div>
          </div>
        </div>
      </div>
      <Footer />
    </div>
  );
}
