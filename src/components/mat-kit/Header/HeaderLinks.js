/*eslint-disable*/
import React from "react";
import DeleteIcon from "@material-ui/icons/Delete";
import IconButton from "@material-ui/core/IconButton";
// react components for routing our app without refresh
import { Link } from "react-router-dom";

// @material-ui/core components
import { makeStyles } from "@material-ui/core/styles";
import List from "@material-ui/core/List";
import ListItem from "@material-ui/core/ListItem";
import Tooltip from "@material-ui/core/Tooltip";

// @material-ui/icons
import { Apps, CloudDownload } from "@material-ui/icons";

// core components
import CustomDropdown from "components/mat-kit/CustomDropdown/CustomDropdown.js";
import Button from "components/mat-kit/CustomButtons/Button.js";

import styles from "assets/mat-kit/jss/material-kit-react/components/headerLinksStyle.js";

const useStyles = makeStyles(styles);

export default function HeaderLinks(props) {
  const classes = useStyles();
  return (
    <List className={classes.list}>
      <ListItem className={classes.listItem}>
        <CustomDropdown
          noLiPadding
          buttonText="Classes"
          buttonProps={{
            className: classes.navLink,
            color: "transparent",
          }}
          buttonIcon={Apps}
          dropdownList={[
            <Link to="/classes/about" className={classes.dropdownLink}>
              About
            </Link>,
            <Link to="/classes/schedule" className={classes.dropdownLink}>
              Schedule
            </Link>,
          ]}
        />
      </ListItem>
      <ListItem className={classes.listItem}>
        <Link to="/" color="transparent" className={classes.navLink}>
          Contact
        </Link>
      </ListItem>
      <ListItem className={classes.listItem}>
        <Link to="/login" color="transparent" className={classes.navLink}>
          Login
        </Link>
      </ListItem>
    </List>
  );
}
