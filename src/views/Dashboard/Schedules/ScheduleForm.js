import { useState, useEffect } from "react";
import "date-fns";
import Grid from "@material-ui/core/Grid";
import Paper from "@material-ui/core/Paper";
import DateFnsUtils from "@date-io/date-fns";
import { TextField } from "@material-ui/core";
import Button from "components/mat-kit/CustomButtons/Button";
import { makeStyles } from "@material-ui/styles";
import {
  MuiPickersUtilsProvider,
  KeyboardTimePicker,
  KeyboardDatePicker,
} from "@material-ui/pickers";
import AccessTimeIcon from "@material-ui/icons/AccessTime";

const useStyles = makeStyles((theme) => ({
  root: {
    flexGrow: 1,
  },
  fullTextField: {
    width: "100%",
  },
  paper: {
    padding: 30,
  },
}));

export default function ScheduleForm(props) {
  const { handleFormData, initData } = props;
  const classes = useStyles();

  const [formData, setFormData] = useState({
    id: "NEW",
    description: "",
    date_time: new Date(),
    recurringInterval: "None",
    zoom_id: "",
    zoom_id_error: null,
    zoom_pass: "",
    zoom_pass_error: null,
    cost: "",
    costError: null
  });

  function setFormDataHelper(data) {
    setFormData((prev) => {
      return { ...prev, ...data };
    });
  }

  useEffect(() => {
    if (!initData) {
      return;
    }
    setFormDataHelper(initData);
  }, []);

  const handleDateChange = (date) => {
    console.log(date);
    setFormDataHelper({ date_time: date });
  };

  function handleTimeChange(time) {
    console.log(time);
    setFormDataHelper({ date_time: time });
  }

  function handleChangeCost(event){
    const cost = event.target.value;
    setFormDataHelper({ cost: cost });
  }
  
  function handleBlurCost(event){
    console.log(event.target.value);
    
    let cost = parseFloat(event.target.value);
    cost = cost.toFixed(2);
    setFormDataHelper({ cost: cost });
  }

  function handleChangeInterval(event) {
    setFormDataHelper({ recurringInterval: event.target.value });
  }

  function handleMainButton() {
    if (initData) {
      handleFormData("UPDATE", formData);
    } else {
      handleFormData("ADD", formData);
    }
  }

  function handleDeleteButton() {
    handleFormData("DELETE", formData);
  }

  return (
    <div className={classes.root}>
      <Paper className={classes.paper}>
        <Grid container spacing={3}>
          <MuiPickersUtilsProvider utils={DateFnsUtils}>
            <Grid item xs={12} md={12}>
              <TextField
                id="description"
                value={formData.description}
                label="Description"
                onChange={(event) => {
                  setFormDataHelper({ description: event.target.value });
                }}
                fullWidth={true}
                multiline={true}
              />
            </Grid>
            <Grid item xs={12} md={4}>
              <KeyboardDatePicker
                margin="normal"
                id="date-picker-dialog"
                label="Date Picker"
                format="MM/dd/yyyy"
                value={formData.date_time}
                onChange={handleDateChange}
                KeyboardButtonProps={{
                  "aria-label": "change date",
                }}
              />
            </Grid>
            <Grid item xs={12} md={4}>
              <KeyboardTimePicker
                margin="normal"
                id="time-picker"
                label="Time picker"
                value={formData.date_time}
                onChange={handleTimeChange}
                KeyboardButtonProps={{
                  "aria-label": "change time",
                }}
                keyboardIcon={<AccessTimeIcon />}
              />
            </Grid>
          </MuiPickersUtilsProvider>
          <Grid item xs={12} md={4}>
            {/* <FormControl style={{ width: 250 }}>
              <InputLabel>Recurring Interval</InputLabel>
              <Select
                value={formData.recurringInterval}
                onChange={handleChangeInterval}
                autoWidth
              >
                <MenuItem value="None">
                  <em>None</em>
                </MenuItem>
                <MenuItem value="Daily">Daily</MenuItem>
                <MenuItem value="Weekly">Weekly</MenuItem>
                <MenuItem value="Monthly">Monthly</MenuItem>
                <MenuItem value="Yearly">Yearly</MenuItem>
              </Select>
            </FormControl> */}
          </Grid>
          <Grid item xs={12} md={4}>
            <TextField
              id="zoom-id"
              label="Zoom Meeting ID"
              onChange={(event) => {
                setFormDataHelper({ zoom_id: event.target.value });
              }}
              value={formData.zoom_id}
              className={classes.fullTextField}
            />
          </Grid>
          <Grid item xs={12} md={4}>
            <TextField
              id="zoom-passcode"
              label="Zoom Passcode"
              onChange={(event) => {
                setFormDataHelper({ zoom_pass: event.target.value });
              }}
              value={formData.zoom_pass}
              className={classes.fullTextField}
            />
          </Grid>
          <Grid item xs={12} md={4} align="center"></Grid>
          <Grid item xs={12} md={12} align="left">
            <Grid container>
              <Grid item xs={8}>
                <TextField
                  id="cost"
                  label="Meeting Cost"
                  onChange={handleChangeCost}
                  onBlur={handleBlurCost}
                  InputProps={{
                    type:"number",
                    min: "0.01",
                    max: "10000.00",
                    step: "0.01"
                  }}
                  value={formData.cost}
                />
              </Grid>
              <Grid item xs={4}>
                <Button
                  variant="contained"
                  size="large"
                  color="warning"
                  onClick={handleMainButton}
                >
                  {initData ? "Update" : "Create New"}
                </Button>
                {initData && (
                  <Button
                    variant="contained"
                    size="large"
                    color="danger"
                    onClick={handleDeleteButton}
                  >
                    Delete
                  </Button>
                )}
              </Grid>
            </Grid>
          </Grid>
        </Grid>
      </Paper>
    </div>
  );
}
