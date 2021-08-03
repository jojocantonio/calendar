<template>
    <div :class="[ data ? data : '']" class="alert alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <strong>Success!</strong> Added to the database.
    </div>
</template>
<script>
  export default {
    name: 'alert-box',
    props: {
        data: {
            type: String,
            default: ''
        },
    }
  }
</script>
<style scoped>
.alert {
  position: absolute;
  top: 2px;
  right: 2px
}
</style>