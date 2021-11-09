import React from "react";
// nodejs library that concatenates classes
import classNames from "classnames";
// @material-ui/core components
import { makeStyles } from "@material-ui/core/styles";

// @material-ui/icons

// core components
import Footer from "components/mat-kit/Footer/Footer.js";
import GridContainer from "components/mat-kit/Grid/GridContainer.js";
import GridItem from "components/mat-kit/Grid/GridItem.js";
import Button from "components/mat-kit/CustomButtons/Button.js";
import Parallax from "components/mat-kit/Parallax/Parallax.js";

import styles from "assets/mat-kit/jss/material-kit-react/views/landingPage.js";

// Sections for this page
import ProductSection from "./Sections/ProductSection.js";
import TeamSection from "./Sections/TeamSection.js";
import WorkSection from "./Sections/WorkSection.js";

import AslHeader from "components/AslHeader.js";
import { Link } from "react-router-dom";

const dashboardRoutes = [];

const useStyles = makeStyles(styles);

export default function LandingPage(props) {
  const classes = useStyles();
  return (
    <div>
      <AslHeader/>
      <Parallax filter image={require("assets/img/stock/two-signing-cafe.jpg").default}>
        <div className={classes.container}>
          <GridContainer>
            <GridItem xs={12} sm={12} md={6}>
              <h1 className={classes.title}>American Sign Language Community and Classes For All</h1>
              {/* <h4>
                Need a blerb here.
              </h4>
              <br /> */}
              <Link to="/login">
              <Button
                color="info"
                size="lg"
                // href="https://www.youtube.com/watch?v=dQw4w9WgXcQ&ref=creativetim"
                target="_blank"
                rel="noopener noreferrer"
              >
                Signup
              </Button>
              </Link>
            </GridItem>
          </GridContainer>
        </div>
      </Parallax>
      <div className={classNames(classes.main, classes.mainRaised)}>
        <div className={classes.container}>
          <ProductSection />
          {/* <TeamSection /> */}
          <div></div>
          <WorkSection />
        </div>
      </div>
      {/* <div className={classNames( classes.main, classes.secondary)}>
        <div className={classes.container}>
          scrolling twitter feed here...
        </div>
      </div> */}
      <Footer />
    </div>
  );
}
