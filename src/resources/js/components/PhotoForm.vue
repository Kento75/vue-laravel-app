<template>
  <div v-show="value" class="photo-form">
    <h2 class="title">Submit a photo</h2>
    <form class="form">
      <input class="form__item" type="file" @change="onFileChange">
      <output class="form__output" v-if="preview">
        <img :src="preview" alt="">
      </output>
      <div class="form__button">
        <button type="submit" class="button button--inverse">submit</button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: Boolean,
      required: true
    }
  },
  data() {
    return {
      preview: null
    }
  },
  methods: {
    // ファイル選択時
    onFileChange(event) {
      if(event.target.files.length === 0) {
        this.reset()  // 何も選択されていない場合、プレビューをリセットする
        return false
      }

      if(!event.target.files[0].type.match("image.*")) {
        this.reset()  // 画像以外が選択された場合、プレビューをリセットする
        return false
      }

      // FileReaderクラス生成
      const reader = new FileReader()

      // ファイル読み込み後処理
      reader.onload = e => {
        this.preview = e.target.result
      }

      // ファイル読み込み
      reader.readAsDataURL(event.target.files[0])
    },

    // 入力欄とプレビューをリセットする
    reset() {
      this.preview = ""
      this.$el.querySelector('input[type="file"]').value = null
    }
  }
}
</script>
