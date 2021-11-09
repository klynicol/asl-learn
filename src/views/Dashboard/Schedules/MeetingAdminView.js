import CustomTabs from "components/mat-kit/CustomTabs/CustomTabs";
import GridContainer from "components/mat-kit/Grid/GridContainer";

import BuildIcon from "@material-ui/icons/Build";
import PeopleIcon from "@material-ui/icons/People";
import CalendarTodayIcon from "@material-ui/icons/CalendarToday";
import ScheduleForm from "./ScheduleForm";
import Utility from "services/Utility";

import { ModalContext } from "services/Modal/modal-context";
import { useContext } from "react";

const classes = {
  textCenter: {
    textAlign: "center",
  },
};

export default function MeetingAmdinView(props) {
  const { handleFormData, meeting, signups } = props;

  const { modal, handleModal } = useContext(ModalContext);

  function handleFormDataHelper(action, formData) {
    if (action == "DELETE") {
      if (meeting.guest_regs.length > 0 || meeting.user_regs.length > 0) {
        handleModal({
          content:
            "This meeting has registrations, are you sure you want to delete it?",
          confirmCallback: (confirm) => {
            if (confirm) {
              handleFormData(action, formData);
            }
          },
        });
      } else {
        handleFormData(action, formData);
      }
    } else if(action == "UPDATE"){
      handleFormData(action, formData);
    }
  }

  function parseRegistrations() {
    if (meeting.guest_regs.length === 0) {
      return <div>no registrations yet</div>;
    }
    return meeting.guest_regs.map((guestReg) => {
      <div></div>;
    });
  }

  function parseMeetingDateTime(dateTime) {
    return (
      <span style={{ fontSize: 20 }}>{Utility.dateTimeString(dateTime)}</span>
    );
  }

  return (
    <GridContainer>
      <CustomTabs
        headerColor="info"
        title={parseMeetingDateTime(meeting.date_time)}
        rtlActive={true}
        tabs={[
          {
            tabName: "People",
            tabIcon: PeopleIcon,
            tabContent: parseRegistrations(),
          },
          {
            tabName: "Settings",
            tabIcon: BuildIcon,
            tabContent: (
              <ScheduleForm
                handleFormData={handleFormDataHelper}
                initData={{
                  id: meeting.id,
                  description: meeting.description,
                  date_time: meeting.date_time,
                  zoom_id: meeting.zoom_id,
                  zoom_pass: meeting.zoom_pass,
                  cost: meeting.cost
                }}
              ></ScheduleForm>
            ),
          },
        ]}
      />
    </GridContainer>
  );
}
