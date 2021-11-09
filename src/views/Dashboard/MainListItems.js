import React from "react";
import ListItem from "@material-ui/core/ListItem";
import ListItemIcon from "@material-ui/core/ListItemIcon";
import ListItemText from "@material-ui/core/ListItemText";
import ListSubheader from "@material-ui/core/ListSubheader";
import DashboardIcon from "@material-ui/icons/Dashboard";
import ShoppingCartIcon from "@material-ui/icons/ShoppingCart";
import PeopleIcon from "@material-ui/icons/People";
import BarChartIcon from "@material-ui/icons/BarChart";
import LayersIcon from "@material-ui/icons/Layers";
import AssignmentIcon from "@material-ui/icons/Assignment";

import CalendarTodayIcon from "@material-ui/icons/CalendarToday";
import MailIcon from "@material-ui/icons/Mail";

export const MainListItems = (props) => {
  const { setPage, page } = props;
  return (
    <div>
      <ListItem
        button
        onClick={() => {
          setPage("SCHEDULES");
        }}
        selected={page === "SCHEDULES"}
      >
        <ListItemIcon>
          <CalendarTodayIcon />
        </ListItemIcon>
        <ListItemText primary="Schedules" />
      </ListItem>
      <ListItem
        button
        onClick={() => {
          setPage("MAIL_LIST");
        }}
        selected={page === "MAIL_LIST"}
      >
        <ListItemIcon>
          <MailIcon />
        </ListItemIcon>
        <ListItemText primary="Mail List" />
      </ListItem>
    </div>
  );
};
