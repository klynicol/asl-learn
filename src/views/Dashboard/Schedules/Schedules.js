import React, { useContext, useState, useEffect } from "react";
import Connection from "services/Connection";
import ScheduleForm from "./ScheduleForm";
import UserCtx from "state/user-context";
import MeetingAdminView from "./MeetingAdminView";

const con = new Connection();

export const Schedules = (props) => {
  const userCtx = useContext(UserCtx);
  const [meetings, setMeetings] = useState({});
  const [signups, setSignups] = useState({});

  useEffect(() => {
    /**
     * TODO, lift state to the dashboard component?
     */
    con.userGet("user/meeting/getall", userCtx.userToken).then((result) => {
      if (!result.data) {
        return;
      }
      setMeetings(
        result.data.reduce((acc, value) => {
          const id = value.id;
          value.cost = parseFloat(value.cost);
          value.date_time = new Date(value.date_time + " UTC");
          acc[id] = value;
          return acc;
        }, {})
      );
    });
  }, []);

  useEffect(() => {
    console.log("meetings", meetings);
  }, [meetings]);

  function handleFormData(action, formData) {
    if (action === "ADD") {
      /**
       * Let the front end transform the data.
       */
      let data = {
        zoom_pass: formData.zoom_pass.trim(),
        zoom_id: formData.zoom_id.trim(),
        description: formData.description.trim(),
        date_time: formData.date_time.toUTCString(), //User timezone
        cost: parseFloat(formData.cost)
      };
      con
      .userPost("user/meeting/create", userCtx.userToken, data)
      .then((result) => {
        let newMeeting = result.data.meeting;
        newMeeting.date_time = formData.date_time;
        setMeetings(prev => {
          prev[newMeeting.id] = newMeeting;
          return {...prev};
        })
      });
    } else if (action === "UPDATE") {
      let data = {
        id: formData.id,
        zoom_pass: formData.zoom_pass.trim(),
        zoom_id: formData.zoom_id.trim(),
        description: formData.description.trim(),
        date_time: formData.date_time.toUTCString(), //User timezone
        cost: parseFloat(formData.cost)
      };
      con
        .userPost("user/meeting/update", userCtx.userToken, data)
        .then((result) => {
          if (!result.data) {
            //TODO alert user, data did not save
            return;
          }
          setMeetings((prev) => {
            prev[formData.id] = { ...prev[formData.id], ...formData };
            return { ...prev };
          });
        });
    } else if (action === "DELETE"){
      con.userGet(`user/meeting/delete?id=${formData.id}`, userCtx.userToken)
      .then(result => {
        console.log(result);
      })
    }
  }

  function renderMeetingList() {
    return Object.keys(meetings).map((id) => {
      let meeting = meetings[id];
      return (
        <MeetingAdminView
          key={id}
          handleFormData={handleFormData}
          meeting={meeting}
        />
      );
    });
  }

  return (
    <div>
      <ScheduleForm handleFormData={handleFormData}></ScheduleForm>
      {renderMeetingList()}
    </div>
  );
};
