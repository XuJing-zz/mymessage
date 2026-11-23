<script>
	export default {
		onLaunch: function() {
			console.log('App Launch')
		},
		onShow: function() {
			console.log('App Show')
		},
		onHide: function() {
			console.log('App Hide')
		}
	}
</script>

<style>
	/*每个页面公共css */
	page {
		background: #f5f6f8;
		color: #1f2329;
		font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", "PingFang SC", "Microsoft YaHei", sans-serif;
	}
	/* H5 下 uni 的 view 默认不是 border-box，统一一下 */
	view, text, navigator, input, textarea, button {
		box-sizing: border-box;
	}
	button::after { border: none; }
	/* H5 下隐藏 scroll-view 的原生滚动条，避免压住内容/看着被裁切 */
	uni-scroll-view ::-webkit-scrollbar,
	.uni-scroll-view::-webkit-scrollbar {
		width: 0 !important;
		height: 0 !important;
		display: none !important;
	}
</style>
