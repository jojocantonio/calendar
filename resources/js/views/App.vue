const { filter } = require("vue/types/umd");
<template>
  <div class="container mt-5">
    <AlertBox v-show="submit_status === 'OK'" data="alert-success" ></AlertBox>
    <h3>Calendar</h3><hr/>
    <div class="row">
      <div class="col-md-5">
        <div class="form-group">
          <label for="event">Event:</label>
          <input type="text" @keyup="reEvaluate" v-model="event" class="form-control" placeholder="ex. practice" id="event">
        </div>
        <div class="form-group grid">
          <label for="from">From:<br/>
          <input type="date" @change="reEvaluate" v-model="from_date" class="form-control" id="from"></label>
          <label for="to">To:<br/>
          <input type="date" @change="reEvaluate" v-model="to_date" class="form-control" id="to"></label>
        </div>
        <div class="checkbox">
          <label><input @change="reEvaluate" type="checkbox" v-model="mon" true-value="Monday"> Mon</label>
          <label><input @change="reEvaluate" type="checkbox" v-model="tue" true-value="Tuesday"> Tue</label>
          <label><input @change="reEvaluate" type="checkbox" v-model="wed" true-value="Wednesday"> Wed</label>
          <label><input @change="reEvaluate" type="checkbox" v-model="thur" true-value="Thursday"> Thur</label>
          <label><input @change="reEvaluate" type="checkbox" v-model="fri" true-value="Friday"> Fri</label>
          <label><input @change="reEvaluate" type="checkbox" v-model="sat" true-value="Saturday"> Sat</label>
          <label><input @change="reEvaluate" type="checkbox" v-model="sun" true-value="Sunday"> Sun</label>
        </div> 
        <button type="submit" @click.prevent="onSubmit" class="btn btn-primary btn-sm">Save</button>
      </div>
      <div class="col-md-7">
        <div id="table">
          <table class="table table-striped" >
            <thead>
              <tr>
                <th colspan="3" style="border-top: none; border-bottom: 3px solid black">{{ getYearMonth }}</th>
              </tr>
            </thead>
            <tbody id="tbody">
              <div v-for="(date, index) in dates" :key="index"> 
                <tr v-for="(td, index) in add_rows(date)" :key="index">
                  <td>{{ td.day }}</td>
                  <td>{{ td.day_name }}</td>
                  <td>{{ td.description }}</td>
                </tr><br/><hr/>
              </div>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  export default {
    data() {
      return {
          event: null,
          from_date: null, to_date: null,
          mon: null, tue: null, wed: null, thur: null, fri: null, sat: null, sun: null,
          error: false,
          submit_status: null,
          on_change: false,
          dates: []
      }
    },
    beforeMount() {
      this.$http.get(`api/calendar`)
      .then(response => {
        this.dates = response.data
        //might wanna end load effect here
      })
    },
    methods: {
      onSubmit () {
        if(this.error){
          this.submit_status = 'ERROR'
        }
        else {
          this.submit_status = 'PENDING'
          this.addEvent()
          .then(response => {
              // this.dates = response.data
              this.submit_status = 'OK'
          })
        }
      },
      reEvaluate() {
        this.on_change = true
  
        let days  = this.getCheckboxFilters();
        let inputsAsDate = [{
          'from': this.getDate(this.from_date),
          'to': this.getDate(this.to_date),
          'event': this.event,
          'days': days
        }]
        this.dates = inputsAsDate
        this.rows = []
      },
      addEvent() {

        let event = this.event
        let from  = this.getDate(this.from_date)
        let to    = this.getDate(this.to_date)
        let days  = this.getCheckboxFilters()

        return this.$http.post(`api/calendar`, {event, from, to, days})
      },
      getCheckboxFilters(){
          return [this.mon, this.tue, this.wed, this.thur, this.fri, this.sat, this.sun];
      },
      getDate(input){
          return input?new Date(input) : new Date() 
      },
      breakdown(date) {
        try{
          this.error = false
          let year = date.getFullYear()
          let month = date.toLocaleString('default', { month: 'long' })

          return {'what_year': year, 'what_month': month}
        }catch(e){ this.error = true} 
      },
      days_difference(from_date,to_date) {
        // To calculate the time difference of two dates 
        let Difference_In_Time = this.getDate(from_date).getTime() - this.getDate(to_date).getTime(); 
        // To calculate the no. of days between two dates 
        let Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);
        //round it - lol
        let difference = Math.abs(Math.round(Difference_In_Days))
        //validate
        let valid = this.getDate(from_date) < this.getDate(to_date) && difference > 0
        //return
        return valid? difference : 0
      },  
      get_days_in_month(month,year){
        return new Date(year, month, 0).getDate();
      },
      // getDatesBetweenDates(startDate, endDate) {
      //   const theDate = new Date(startDate)
      //   this.dates = [];
      //   while (theDate < endDate) {
      //     this.dates.push(new Date(theDate))
      //     theDate.setDate(theDate.getDate() + 1)
      //   }
      //   return this.dates
      // },
      getDayName(day) {
        let days = this.getWeekDays()
        return days[day]
      },
      getWeekDays(){
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
      },
      getDescriptionByFilter(day, description, filters){
        let has_filter = false
        let filters_str = filters.toString().replace(/[[\]"]/g,'')
        let filters_arr = filters_str.split(',')

        let week_days = this.getWeekDays()

        for(let i=0; i < filters_arr.length; i++){
          if(filters_arr[i] == week_days[day]){
              has_filter = true
              return description
          }
          if(has_filter) break
          has_filter = false
        }
        return '-'
      },
      add_rows(date) {
        //get each day from start to end date
        const theDate = this.getDate(date.from)
        const endDate = this.getDate(date.to)

        let localOrDatabaseEvent = this.on_change? this.event : date.event
        let localOrDatabaseDays = this.on_change? this.getCheckboxFilters() : date.days
        let filters = date.days
        let rows = []
        
        while (theDate <= endDate) {
          let dataForEachLine = {}
          dataForEachLine.day = theDate.getDate()
          dataForEachLine.day_name = this.getDayName(theDate.getDay())

          let description = this.getDescriptionByFilter(theDate.getDay(), localOrDatabaseEvent, localOrDatabaseDays)
          dataForEachLine.description = description

          rows.push(dataForEachLine)
          theDate.setDate(theDate.getDate() + 1)  //icrement each day
        }
        this.on_change = false;

        return rows
      }

    },
    computed: {
      from() {
        let date = this.getDate(this.from_date)
        return this.breakdown(date)
      },
      to() {
        let date = this.getDate(this.to_date)
        return this.breakdown(date)  
      },
      getYearMonth(){
        if(!this.from_date && !this.to_date){
          return this.from.what_month + ' ' + this.from.what_year 
        }
        return this.from.what_month + ' ' + this.from.what_year + ' - ' + this.to.what_month + ' ' + this.to.what_year
      },
      days_count() {
        return this.days_difference(this.from_date, this.to_date)
      },
      getDates() {
        let start = this.getDate(this.from_date)
        let end = this.getDate(this.to_date)
        return this.getDatesBetweenDates(start, end)
      }
    }
  }
</script>
